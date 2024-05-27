# Fixtures

Fixtures are generated using [zenstruck/foundry](https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html)
and Doctrine Fixtures Bundle.

## Loading Fixtures

```shell
make db.fixtures
```

will generate the fixtures in the database, erasing previous data.

## Generating a new factory

https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories

```shell
symfony console make:factory --namespace=Infra\\Fixtures\\Factory --all-fields
```

**Hint:** do not provide default value for uid fields, but let your classes' constructor generate one for dev fixtures.

## Tests

For writing & loading fixtures in tests,
see [Using fixtures in your tests](https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#using-in-your-tests)
.

Basically, most of your test cases should define a dedicated [story](https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#stories) 
in a `[test-case-path]/[test-class-name]/fixtures` directory and be loaded like this:

```diff
+use App\Tests\Functional\GraphQL\ListTest\fixtures\ListStory;
+use Zenstruck\Foundry\Test\Factories;
+use Zenstruck\Foundry\Test\ResetDatabase;

class ListTest extends GraphQLTestCase
{
+    use ResetDatabase;
+    use Factories;
+
    public function testFind(): void
    {
+        ListStory::load(); // Loading your story fixtures

        $this->executeGraphQL(['uid' => '01G8K8HGD0R2PKD4W2RZMX259M']);

        $this->assertValidGraphQlResponse();
        $this->assertJsonResponseMatchesExpectations();
    }
}
```

On contrary of the fixtures for the dev env, you should avoid using random values from Faker, but set specific values
for each of the properties you'd like to test in your test cases.
