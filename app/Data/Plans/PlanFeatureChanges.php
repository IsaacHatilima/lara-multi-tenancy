<?php

declare(strict_types=1);

namespace App\Data\Plans;

/**
 * What a save changes in the features a plan includes. Every map is keyed by feature key; a null limit means unlimited.
 */
final readonly class PlanFeatureChanges
{
    /**
     * @param  array<string, int|null>  $added  the limits the new features get
     * @param  array<string, int|null>  $removed  the limits the removed features had
     * @param  array<string, int|null>  $relimited  the new limits of features the plan keeps
     * @param  array<string, int|null>  $before  every limit the plan had
     */
    private function __construct(
        public array $added,
        public array $removed,
        public array $relimited,
        private array $before,
    ) {
    }

    /**
     * `array_diff_assoc` compares limits as strings, which still tells unlimited (null) from 0.
     *
     * @param  array<string, int|null>  $before
     * @param  array<string, int|null>  $after
     */
    public static function between(array $before, array $after): self
    {
        return new self(
            added: array_diff_key($after, $before),
            removed: array_diff_key($before, $after),
            relimited: array_diff_assoc(array_intersect_key($after, $before), $before),
            before: $before,
        );
    }

    public function isEmpty(): bool
    {
        return $this->added === [] && $this->removed === [] && $this->relimited === [];
    }

    /**
     * @return list<string>
     */
    public function keys(): array
    {
        return array_keys($this->added + $this->removed + $this->relimited);
    }

    /**
     * @return array{
     *     added: list<array{key: string, limit: int|null}>,
     *     removed: list<array{key: string, limit: int|null}>,
     *     relimited: list<array{key: string, old: int|null, new: int|null}>,
     * }
     */
    public function toLog(): array
    {
        return [
            'added' => self::limitEntries($this->added),
            'removed' => self::limitEntries($this->removed),
            'relimited' => array_map(
                fn (string $key, ?int $limit): array => ['key' => $key, 'old' => $this->before[$key], 'new' => $limit],
                array_keys($this->relimited),
                $this->relimited,
            ),
        ];
    }

    /**
     * @param  array<string, int|null>  $limits
     *
     * @return list<array{key: string, limit: int|null}>
     */
    private static function limitEntries(array $limits): array
    {
        return array_map(fn (string $key, ?int $limit): array => ['key' => $key, 'limit' => $limit], array_keys($limits), $limits);
    }
}
