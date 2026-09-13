<?php

declare(strict_types=1);

final class TestHarness
{
    private int $passed = 0;
    private int $failed = 0;

    public function assertTrue(bool $condition, string $message = 'Expected condition to be true'): void
    {
        if (! $condition) {
            throw new RuntimeException($message);
        }
    }

    public function assertSame(mixed $expected, mixed $actual, string $message = ''): void
    {
        if ($expected !== $actual) {
            $prefix = $message === '' ? '' : $message . ': ';
            throw new RuntimeException(
                $prefix . sprintf('expected %s, got %s', var_export($expected, true), var_export($actual, true))
            );
        }
    }

    /** @param array<string, callable(self): void> $tests */
    public function run(array $tests): int
    {
        foreach ($tests as $name => $test) {
            try {
                $test($this);
                $this->passed++;
                echo "PASS $name\n";
            } catch (Throwable $throwable) {
                $this->failed++;
                fwrite(STDERR, "FAIL $name: {$throwable->getMessage()}\n");
            }
        }

        printf("Result: %d passed, %d failed\n", $this->passed, $this->failed);

        return $this->failed === 0 ? 0 : 1;
    }
}
