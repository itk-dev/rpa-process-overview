<?php

namespace App\Validator;

use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

final class ProcessOverviewOptionsValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var ProcessOverviewOptions $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        $resolver = (new OptionsResolver())
            ->setRequired('metadata_columns')
            ->setAllowedTypes('metadata_columns', ['array'])
            ->setOptions('metadata_columns', function (OptionsResolver $metadataColumnsResolver) {
                $metadataColumnsResolver
                    ->setPrototype(true)
                    ->setRequired('label')
                    ->setRequired('data')
                    ->setDefault('mask', [])
                    // https://github.com/symfony/symfony/issues/39569#issuecomment-748504986
                    // https://symfony.com/doc/current/components/options_resolver.html#nested-options
                    ->setAllowedTypes('mask', ['array'])
                    ->setOptions('mask', function (OptionsResolver $maskOptions) {
                        $maskOptions
                            ->setDefault('search', null)
                            ->setAllowedTypes('search', ['null', 'string'])
                            ->setDefault('replace', null)
                            ->setAllowedTypes('replace', ['null', 'string']);
                    })
                    ->setDefault('is_filterable', false)
                    ->setAllowedTypes('is_filterable', ['bool'])
                ;
            })

            ->setRequired('data')
            ->setAllowedTypes('data', ['array'])
            ->setOptions('data', function (OptionsResolver $dataResolver) {
                $dataResolver
                    ->setRequired('title')
                    ->setAllowedTypes('title', ['string'])
                    ->setDefault('page_size', 10)
                    ->setAllowedTypes('page_size', ['integer'])
                    ->setDefault('default_query', [])
                    ->setAllowedTypes('default_query', ['array'])
                    ->setAllowedValues('default_query', static fn (array $value) => !array_is_list($value))
                ;
            })

            ->setRequired('search')
            ->setAllowedTypes('search', ['array'])
            ->setOptions('search', function (OptionsResolver $clientOptionsResolver) {
                $clientOptionsResolver
                    ->setRequired('title')
                    ->setAllowedTypes('title', ['string'])
                ->setDefault('minimum_search_query_length', 3)
                    ->setAllowedTypes('minimum_search_query_length', ['integer'])
                ;
            })
        ;

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
