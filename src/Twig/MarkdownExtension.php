<?php

namespace App\Twig;

use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Environment\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MarkdownExtension extends AbstractExtension
{
    private ?MarkdownConverter $converter = null;

    public function getFilters(): array
    {
        return [
            new TwigFilter('markdown', [$this, 'toHtml'], ['is_safe' => ['html']]),
        ];
    }

    public function toHtml(?string $markdown): string
    {
        if (!$markdown) {
            return '';
        }
        $this->converter ??= $this->buildConverter();
        return (string) $this->converter->convert($markdown);
    }

    private function buildConverter(): MarkdownConverter
    {
        $env = new Environment([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 50,
        ]);
        $env->addExtension(new CommonMarkCoreExtension());
        $env->addExtension(new GithubFlavoredMarkdownExtension());
        $env->addExtension(new AutolinkExtension());

        return new MarkdownConverter($env);
    }
}
