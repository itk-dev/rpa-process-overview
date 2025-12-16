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

        $resolver = new OptionsResolver();
        $resolver->define('metadata_columns')
            ->required()
            ->allowedTypes('array')
            ->options(function (OptionsResolver $metadataColumnsResolver) {
                $metadataColumnsResolver->setPrototype(true);

                $metadataColumnsResolver->define('label')
                    ->required()
                    ->allowedTypes('string');

                $metadataColumnsResolver->define('data')
                    ->required()
                    ->allowedTypes('string');

                $metadataColumnsResolver->define('mask')
                    // https://github.com/symfony/symfony/issues/39569#issuecomment-748504986
                    // https://symfony.com/doc/current/components/options_resolver.html#nested-options
                    ->allowedTypes('array')
                    ->options(function (OptionsResolver $maskOptions) {
                        $maskOptions->define('search')
                            ->allowedTypes('null', 'string');

                        $maskOptions->define('replace')
                            ->allowedTypes('null', 'string');
                    })
                    ->default([]);

                $metadataColumnsResolver->define('is_filterable')
                    ->allowedTypes('bool')
                    ->default(false);
            });

        $resolver->define('data')
            ->required()
            ->allowedTypes('array')
            ->options(function (OptionsResolver $dataResolver) {
                $dataResolver->define('title')
                    ->allowedTypes('string')
                    ->default(10);

                $dataResolver->define('page_size')
                    ->allowedTypes('integer')
                    ->default(10);

                $dataResolver->define('default_query')
                    ->allowedTypes('array')
                    ->default([])
                    ->allowedValues(static fn (array $value) => !array_is_list($value));
            });

        $resolver->define('search')
            ->required()
            ->allowedTypes('array')
            ->options(function (OptionsResolver $clientOptionsResolver) {
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
