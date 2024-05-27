<?php

declare(strict_types=1);

namespace App\Infrastructure\Fixtures\Factory;

use App\Domain\User\User;
use App\Infrastructure\User\Repository\UserRepository;

use function Symfony\Component\String\u;

use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Instantiator;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<User>
 *
 * @method        User|Proxy<User>               create(array|callable $attributes = [])
 * @method static User|Proxy<User>               createOne(array $attributes = [])
 * @method static User|Proxy<User>               find(object|array|mixed $criteria)
 * @method static User|Proxy<User>               findOrCreate(array $attributes)
 * @method static User|Proxy<User>               first(string $sortedField = 'id')
 * @method static User|Proxy<User>               last(string $sortedField = 'id')
 * @method static User|Proxy<User>               random(array $attributes = [])
 * @method static User|Proxy<User>               randomOrCreate(array $attributes = [])
 * @method static UserRepository|RepositoryProxy repository()
 * @method static User[]|Proxy<User>[]           all()
 * @method static User[]|Proxy<User>[]           createMany(int $number, array|callable $attributes = [])
 * @method static User[]|Proxy<User>[]           createSequence(iterable|callable $sequence)
 * @method static User[]|Proxy<User>[]           findBy(array $attributes)
 * @method static User[]|Proxy<User>[]           randomRange(int $min, int $max, array $attributes = [])
 * @method static User[]|Proxy<User>[]           randomSet(int $number, array $attributes = [])
 */
final class UserFactory extends ModelFactory
{
    /**
     * Use this pre-hashed password, so we don't need to hash it every time we create a user in fixtures,
     * which is time-consuming.
     * Plain-text value is "password".
     */
    public const HASHED_PASSWORD = '$2y$13$9qd8w42rFsFK04q0hwdwt.cT2XvGu6RNRAkKBHHg10Zg1ke1K/fTS';
    public const PLAINTEXT_PASSWORD = 'password';

    public const ULID_ADMIN = '01H3CFAYHK2EV1KYXYQ21EW7XR';
    public const EMAIL_ADMIN = 'admin@example.com';

    public const ULID_USER = '01H3CFB8XBSJDCQSHKB8PJXQBX';
    public const EMAIL_USER = 'user@example.com';

    public const ULID_INACTVE_USER = '01H5H7P6A51A4QPPP3ZN5P8G06';
    public const EMAIL_INACTIVE_USER = 'inactive@example.com';

    protected function initialize(): static
    {
        // Allow to set the status when creating an import fixture, even without the setter
        return parent::initialize()->instantiateWith(
            (new Instantiator())->alwaysForceProperties(['uid']),
        );
    }

    protected function getDefaults(): array
    {
        return [
            'password' => self::PLAINTEXT_PASSWORD,
            'firstname' => $firstname = self::faker()->firstName(),
            'lastname' => $lastname = self::faker()->lastName(),
            'email' => sprintf(
                '%s.%s@%s',
                self::nameExtract($firstname),
                self::nameExtract($lastname),
                self::faker()->safeEmailDomain(),
            ),
            'active' => self::faker()->boolean(),
            'admin' => self::faker()->boolean(),
            'createdAt' => $createdAt = self::faker()->dateTimeBetween('-90 days'),
            'updatedAt' => self::faker()->dateTimeBetween($createdAt->format(\DateTime::RFC3339)),
        ];
    }

    protected static function getClass(): string
    {
        return User::class;
    }

    /**
     * Creates default base users for dev and tests purposes.
     */
    public static function baseUsers(bool $hashedPassword = true): void
    {
        $password = $hashedPassword ? self::HASHED_PASSWORD : self::PLAINTEXT_PASSWORD;

        self::new()->create([
            'uid' => new Ulid(self::ULID_ADMIN),
            'firstname' => 'Joe',
            'lastname' => 'Admin',
            'password' => $password,
            'email' => self::EMAIL_ADMIN,
            'admin' => true,
            'active' => true,
            'createdAt' => new \DateTime('2020-01-01 00:00:00'),
        ]);

        self::new()->create([
            'uid' => new Ulid(self::ULID_USER),
            'firstname' => 'John',
            'lastname' => 'ADV',
            'password' => $password,
            'email' => self::EMAIL_USER,
            'admin' => false,
            'active' => true,
            'createdAt' => new \DateTime('2020-01-01 00:00:00'),
        ]);

        self::new()->create([
            'uid' => new Ulid(self::ULID_INACTVE_USER),
            'firstname' => 'Joy',
            'lastname' => 'ADV',
            'password' => $password,
            'email' => self::EMAIL_INACTIVE_USER,
            'admin' => false,
            'active' => false,
            'createdAt' => new \DateTime('2020-01-01 00:00:00'),
        ]);
    }

    private static function nameExtract(string $name): string
    {
        $name = u($name)->ascii()->toString(); // remove accents
        $length = self::faker()->numberBetween(0, -min(max(0, mb_strlen($name) - 1), 3));

        return mb_strtolower(mb_substr($name, 0, $length === 0 ? null : $length));
    }
}
