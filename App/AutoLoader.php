<?php
namespace App;

/**
 * Class AutoLoader
 *
 * @since 2.0
 */
class AutoLoader
{
    /**
     * @return array
     */
    public function getPrefixDirs(): array
    {
        return [
            __NAMESPACE__ => __DIR__,
        ];
    }

    /**
     * @return array
     */
    public function metadata(): array
    {
        return [];
    }
}
