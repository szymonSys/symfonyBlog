<?php
/**
 * Markdown helper.
 */
namespace App\Service;

use Michelf\MarkdownInterface;

/**
 * Class MarkdownHelper.
 */
class MarkdownHelper
{
    /**
     * Markdown.
     *
     * @var \Michelf\MarkdownInterface
     */
    private $markdown;

    /**
     * MarkdownHelper constructor.
     *
     * @param \Michelf\MarkdownInterface $markdown Markdown
     */
    public function __construct(MarkdownInterface $markdown)
    {
        $this->markdown = $markdown;
    }

    /**
     * Parse string.
     *
     * @param string $source Source string
     *
     * @return string Result
     */
    public function parse(string $source): string
    {
        return $this->markdown->transform($source);
    }
}