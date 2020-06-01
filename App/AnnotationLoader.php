<?php
namespace App;
use Framework\SwServer\Annotation\Contract\AnnotationLoaderInterface;
class AnnotationLoader implements AnnotationLoaderInterface
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
