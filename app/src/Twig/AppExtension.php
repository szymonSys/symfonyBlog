<?php
/**
 * Twig application extensions.
 */
namespace App\Twig;

use App\Service\MarkdownHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class AppExtension.
 */
class AppExtension extends AbstractExtension
{
    /**
     * Markdown helper.
     *
     * @var \App\Service\MarkdownHelper
     */
    private $markdownHelper;

    /**
     * AppExtension constructor.
     *
     * @param \App\Service\MarkdownHelper $markdownHelper Markdown helper
     */
    public function __construct(MarkdownHelper $markdownHelper)
    {
        $this->markdownHelper = $markdownHelper;
    }

    /**
     * Get Twig filters.
     *
     * @return array List of filters
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'markdown',
                [$this, 'processMarkdown'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Process Markdown.
     *
     * @param string $value Input string
     *
     * @return string Result
     */
    public function processMarkdown(string $value): string
    {
        return $this->markdownHelper->parse($value);
    }
}