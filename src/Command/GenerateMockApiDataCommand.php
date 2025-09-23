<?php

namespace App\Command;

use Faker;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:generate:mock-api-data',
    description: 'Generate mock API data for https://github.com/dotronglong/faker',
)]
class GenerateMockApiDataCommand extends Command
{
    private SymfonyStyle $io;
    private Faker\Generator $faker;
    private array $paths;

    public function __construct(
        private readonly Filesystem $fileSystem,
        private array $options,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->faker = Faker\Factory::create();
        $this->faker->seed($this->options['seed']);

        $this->paths = [];
        $this->generateProcess(10, 4);

        $this->generateIndex();

        $this->io->writeln(json_encode($this->paths, JSON_PRETTY_PRINT));

        return Command::SUCCESS;
    }

    private function generateProcess(int $pageSize, int $numberOfPages): void
    {
        $computePathAndFilename = static function (?int $page) {
            return $page > 0
                ? [
                    sprintf('/api/v1/process?page=%d', $page),
                    sprintf('mocks/api/v1/process-page-%d.json', $page),
                ]
                : [
                    '/api/v1/process',
                    'mocks/api/v1/process.json',
                ];
        };

        $processId = 0;
        for ($page = 1; $page <= $numberOfPages; ++$page) {
            $body = [
            ];
            for ($id = 1; $id <= $pageSize; ++$id) {
                ++$processId;
                $item = [
                    'id' => $processId,
                    'name' => $this->faker->sentence(4),
                    'metadata' => [
                        'cpr' => 'string',
                        'name' => 'string',
                        'branch' => 'string',
                    ],
                ];
                $numberOfSteps = $this->faker->numberBetween(2, 7);
                for ($s = 1; $s <= $numberOfSteps; ++$s) {
                    $item['steps'][] = [
                        'id' => $s,
                        'name' => $this->faker->sentence(2),
                    ];
                }
                $this->generateProcessRun($item['steps'], $processId);

                $body[] = $item;
            }

            // https://github.com/dotronglong/faker/wiki/Complete-Schema
            $mock = [
                'request' => [
                    'method' => 'GET',
                ],
                'response' => [
                    'body' => $body,
                ],
            ];

            [$path, $filename] = $computePathAndFilename($page);
            $mock['request']['path'] = $path;
            $this->paths[] = $path;
            $this->fileSystem->dumpFile($filename, json_encode($mock, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            // Allow GET'ing the first page without specifying page=1
            if (1 === $page) {
                [$path, $filename] = $computePathAndFilename(null);
                $mock['request']['path'] = $path;
                array_unshift($this->paths, $path);
                $this->fileSystem->dumpFile($filename, json_encode($mock, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            }

            $this->io->info($path);
        }
    }

    private function generateIndex()
    {
        sort($this->paths);

        $mock = [
            'request' => [
                'method' => 'GET',
            ],
            'response' => [
                'body' => $this->paths,
            ],
        ];

        [$path, $filename] = ['/', 'mocks/index.json'];
        $mock['request']['path'] = $path;

        $this->fileSystem->dumpFile($filename, json_encode($mock, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function generateProcessRun(array $steps, int $processId): void
    {
        $computePathAndFilename = static function (?int $page) use ($processId) {
            return $page > 0
                ? [
                    sprintf('/api/v1/process/%s/run?page=%d', $processId, $page),
                    sprintf('mocks/api/v1/process/%s/run-page-%d.json', $processId, $page),
                ]
                : [
                    sprintf('/api/v1/process/%s/run', $processId),
                    sprintf('mocks/api/v1/process/%s/run.json', $processId),
                ];
        };

        $generateSteps = function () use ($steps): array {
            $run = [];

            $failedAt = $this->faker->numberBetween(0, count($steps) + 1);
            $startedAt = new \DateTimeImmutable();
            foreach ($steps as $index => $step) {
                $startedAt = $startedAt->modify(sprintf('+%d seconds', $this->faker->numberBetween(1, 1000)));
                $finishedAt = $startedAt->modify(sprintf('+%d seconds', $this->faker->numberBetween(3, 42)));
                $status = $index < $failedAt ? 'SUCCESS' : 'PENDING';
                $failure = null;
                if ($index === $failedAt) {
                    $status = 'FAILED';
                    $failure = [
                        'code' => 'Fejlkode',
                        'message' => 'Fejlbesked',
                        'retryable' => $this->faker->boolean(),
                        'occurred_at' => $finishedAt->modify(sprintf('-%d seconds', $this->faker->numberBetween(0, 3)))->format(\DateTimeImmutable::ATOM),
                    ];
                }
                $run[] = [
                    'status' => $status,
                    'started_at' => $startedAt->format(\DateTimeImmutable::ATOM),
                    'finished_at' => $finishedAt->format(\DateTimeImmutable::ATOM),
                    'failure' => $failure,
                ];
            }

            return $run;
        };

        $numberOfPages = $this->faker->numberBetween(2, 7);
        $pageSize = 10;
        $runId = 0;
        for ($page = 1; $page <= $numberOfPages; ++$page) {
            $body = [
            ];
            for ($id = 1; $id <= $pageSize; ++$id) {
                ++$runId;

                $item = [
                    'process_id' => $processId,
                    'id' => 10_000 * $processId + $runId,
                    'name' => $this->faker->sentence(4),
                    'metadata' => [
                        'cpr' => $this->faker->randomNumber(),
                        'name' => $this->faker->name(),
                        'branch' => $this->faker->company(),
                    ],
                    'steps' => $generateSteps(),
                ];

                $body[] = $item;
            }

            $mock = [
                'request' => [
                    'method' => 'GET',
                ],
                'response' => [
                    'body' => $body,
                ],
            ];

            [$path, $filename] = $computePathAndFilename($page);
            $mock['request']['path'] = $path;
            $this->paths[] = $path;
            $this->fileSystem->dumpFile($filename,
                json_encode($mock, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            // Allow GET'ing the first page without specifying page=1
            if (1 === $page) {
                [$path, $filename] = $computePathAndFilename(null);
                $mock['request']['path'] = $path;
                array_unshift($this->paths, $path);
                $this->fileSystem->dumpFile($filename,
                    json_encode($mock, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            }

            $this->io->info($path);
        }
    }
}
