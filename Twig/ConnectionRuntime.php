<?php

namespace EMS\SubmissionBundle\Twig;

use EMS\SubmissionBundle\Connection\Transformer;
use Twig\Extension\RuntimeExtensionInterface;

class ConnectionRuntime implements RuntimeExtensionInterface
{
    /** @var Transformer */
    private $transformer;

    public function __construct(Transformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function transform($content): string
    {
        $path = preg_split('/%\.%/', $content);
        return $this->transformer->transform($path);
    }
}