<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\Symfony\Validator\Constraint;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EntityReferenceDoesNotExistValidator extends ConstraintValidator
{
    public function __construct(private readonly EntityManagerInterface $manager)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof EntityReferenceDoesNotExist) {
            throw new UnexpectedTypeException($constraint, EntityReferenceDoesNotExist::class);
        }

        if ($value === null) {
            // noop
            return;
        }

        $repository = $this->manager->getRepository($constraint->entity);

        $repositoryMethod = $constraint->repositoryMethod;

        $foundObject = $repositoryMethod !== null
            // @phpstan-ignore-next-line
            ? $repository->{$repositoryMethod}($value)
            : $repository->findOneBy([$constraint->identityField => $value])
        ;

        if ($foundObject === null) {
            return;
        }

        // Compare found object by injected object:
        if ($constraint->currentObjectPath !== null) {
            $context = $this->context->getObject();

            \assert($context !== null);

            $current = PropertyAccess::createPropertyAccessor()->getValue($context, $constraint->currentObjectPath);

            if ($current === $foundObject) {
                // Current object is the same as the found one, so it's ok
                return;
            }
        }

        // Compare found object by custom comparison method:
        if ($constraint->currentObjectComparisonMethod !== null) {
            $context = $this->context->getObject();

            \assert($context !== null);

            /**
             * @var callable $comparisonFunction
             *
             * @phpstan-var callable(object) $comparisonFunction
             */
            $comparisonFunction = PropertyAccess::createPropertyAccessor()->getValue(
                $context,
                $constraint->currentObjectComparisonMethod,
            );

            if (true === $comparisonFunction($foundObject)) {
                // Current object is the same as the found one, so it's ok
                return;
            }
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $this->formatValue($value))
            ->setCode($constraint->code ?? EntityReferenceDoesNotExist::REF_DOES_NOT_EXIST)
            ->addViolation()
        ;
    }
}
