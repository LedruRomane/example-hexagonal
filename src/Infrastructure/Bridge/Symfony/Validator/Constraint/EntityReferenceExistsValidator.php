<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\Symfony\Validator\Constraint;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EntityReferenceExistsValidator extends ConstraintValidator
{
    public function __construct(private readonly ManagerRegistry $registry)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof EntityReferenceExists) {
            throw new UnexpectedTypeException($constraint, EntityReferenceExists::class);
        }

        if ($value === null || $value === '') {
            // noop
            return;
        }

        if (null === $manager = $this->registry->getManagerForClass($constraint->entity)) {
            return;
        }

        $repository = $manager->getRepository($constraint->entity);

        if (null === $repository->findOneBy([$constraint->identityField => $value])) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(EntityReferenceExists::REF_DOES_NOT_EXIST)
                ->addViolation();
        }
    }
}
