<?php

namespace App\Validator;

use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

final class DataSourceOptionsValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var DataSourceOptions $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        $resolver = (new OptionsResolver())
            ->setRequired('client_options')
            ->setAllowedTypes('client_options', ['array'])
            ->setOptions('client_options', function (OptionsResolver $clientOptionsResolver) {
                $clientOptionsResolver
                    ->setDefault('headers', [])
                    ->setAllowedTypes('headers', ['array'])
                    ->setAllowedValues('headers', static fn (array $headers): bool => !array_is_list($headers));
            });

        try {
            $options = Yaml::parse($value);
            $resolver->resolve($options);
        } catch (ParseException $parseException) {
            $this->context->buildViolation($constraint->invalidYamlMessage)
                ->setParameter('{{ message }}', $parseException->getMessage())
                ->addViolation();
        } catch (ExceptionInterface $exception) {
            $this->context->buildViolation($constraint->invalidConfigMessage)
                ->setParameter('{{ message }}', $exception->getMessage())
                ->addViolation();
        }
    }
}
