# Working with ULIDs & Database

## Database

The ULIDs are store in binary format in the database, which might not be very handy to work with at first sight.

Convert to hexadecimal using `HEX`:

```sql
SELECT uid, HEX(uid)
FROM item
```

| ULID               | HEX(ULID) |
|--------------------|---|
| `b"\x01‚$èv\x00úåàî"®)V,\x11"` | 018224E87600FAE5E0EE22AE29562C11 |
| `b"\x01‚$èv\x08z"ÿ\x11lÜLõI "` | 018224E876087A22FF116CDC4CF54920 |

Query using the HEX value:

```sql
SELECT uid
FROM item
WHERE uid = 0x018224E876087A22FF116CDC4CF54921
```

or

```sql
SELECT uid
FROM item
WHERE uid = UNHEX('018224E876087A22FF116CDC4CF54921')
```

> **Note**
> For simplicity, we added a `uid32` column each table in the database, 
> exposing the ULID in its canonical, base 32 format.

## Debug Command

A `ulid:inspect` command is available to print the ULID in multiple formats:

```shell
symfony console ulid:inspect 018224E876087A22FF116CDC4CF54920
```

outputs:

```shell
 ---------------------- --------------------------------------
  Label                  Value
 ---------------------- --------------------------------------
  toBase32 (canonical)   01G8JEGXG8F8HFY4BCVH6FAJ90
  toBase58               1BoagzrNVdgKE3NCKj1Joq
  toRfc4122              018224e8-7608-7a22-ff11-6cdc4cf54920
  toBinary               b"\x01‚$èv\x08z"ÿ\x11lÜLõI "
  toHex                  0x018224e876087a22ff116cdc4cf54920
 ---------------------- --------------------------------------
  Time                   2022-07-22 07:56:30.600 UTC
 ---------------------- --------------------------------------
```

## Repositories

You shouldn't usually care much about ULIDs specificities when working with repositories, since `findBy` methods usually
works directly with the ULID instance:

```php
$uid = Ulid::fromString($string);

$this->cardRepository->findOneBy(['uid' => $uid))
$this->cardRepository->findOneByUid($uid);
```

But when writing your own query builder, you need to provide extra hints:

```php
public function findOneByUid(Ulid $uid)
{
    return $this->createQueryBuilder('item')
        ->where('item.uid = :uid')
        ->setParameter('uid', $uid, 'ulid') // <-- use the ULID type
        ->getQuery()->getOneOrNullResult()
    ;
}
```

Altrnatively, use the binary format directly:

```php

public function findOneByUid(Ulid $uid)
{
    return $this->createQueryBuilder('item')
        ->where('item.uid = :uid')
        ->setParameter('uid', $uid->toBinary()) // <-- convert to binary
        ->getQuery()->getOneOrNullResult()
    ;
}
```
