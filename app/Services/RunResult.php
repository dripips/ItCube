<?php

namespace App\Services;

use JsonSerializable;

/**
 * Итог одного запуска кода.
 */
final class RunResult implements JsonSerializable
{
    public const STATUS_COMPLETED = 'completed';

    public const STATUS_ERROR = 'error';

    public const STATUS_BLOCKED = 'blocked';

    private function __construct(
        public readonly bool $success,
        public readonly string $output,
        public readonly string $status,
        public readonly int $runtimeMs = 0,
    ) {}

    public static function completed(string $output, int $runtimeMs = 0): self
    {
        return new self(true, $output, self::STATUS_COMPLETED, $runtimeMs);
    }

    /** Код выполнился, но завершился ошибкой — или не собрался. */
    public static function failed(string $output, int $runtimeMs = 0): self
    {
        return new self(false, $output, self::STATUS_ERROR, $runtimeMs);
    }

    /** Код до площадки не доехал: не прошёл проверку до отправки. */
    public static function blocked(string $reason): self
    {
        return new self(false, $reason, self::STATUS_BLOCKED);
    }

    public function jsonSerialize(): array
    {
        return [
            'success' => $this->success,
            'output' => $this->output,
            'status' => $this->status,
            'runtime_ms' => $this->runtimeMs,
        ];
    }
}
