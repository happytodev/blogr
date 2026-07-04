<?php

namespace Happytodev\Blogr\Rendering;

use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use Spatie\ShikiPhp\Shiki;
use Symfony\Component\Process\ExecutableFinder;

class ShikiCodeBlockRenderer implements NodeRendererInterface
{
    private string $lightTheme;

    private string $darkTheme;

    private static ?string $nodeBinary = null;

    private bool $showLineNumbers;

    public function __construct(
        ?string $lightTheme = null,
        ?string $darkTheme = null,
        ?bool $showLineNumbers = null,
    ) {
        $this->lightTheme = $lightTheme ?? (string) config('blogr.shiki.light_theme', 'github-light');
        $this->darkTheme = $darkTheme ?? (string) config('blogr.shiki.dark_theme', 'github-dark');
        $this->showLineNumbers = $showLineNumbers ?? (bool) config('blogr.shiki.line_numbers', true);
    }

    public static function resolveNodeBinary(): ?string
    {
        if (self::$nodeBinary !== null) {
            return self::$nodeBinary;
        }

        $finder = new ExecutableFinder;

        $candidates = [
            '/opt/homebrew/bin',
            '/usr/local/bin',
            getenv('HOME').'/n/bin',
        ];

        $nvmRoot = getenv('NVM_DIR') ?: (getenv('HOME').'/.nvm');
        if (is_dir($nvmRoot.'/versions')) {
            $versions = glob($nvmRoot.'/versions/node/*/bin', GLOB_ONLYDIR) ?: [];
            foreach ($versions as $bin) {
                $candidates[] = $bin;
            }
        }

        $herdPath = getenv('HOME').'/Library/Application Support/Herd/config/nvm/versions/node';
        if (is_dir($herdPath)) {
            $versions = glob($herdPath.'/*/bin', GLOB_ONLYDIR) ?: [];
            foreach ($versions as $bin) {
                $candidates[] = $bin;
            }
        }

        /* @phpstan-ignore-next-line */
        return self::$nodeBinary = $finder->find('node', null, $candidates);
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if ($node instanceof FencedCode) {
            $info = $node->getInfo();
            $words = $info !== null ? preg_split('/\s+/', $info) : [];
            $language = ! empty($words[0]) ? $words[0] : 'text';
            $code = $node->getLiteral();
        } elseif ($node instanceof IndentedCode) {
            $language = 'text';
            $code = $node->getLiteral();
        } else {
            throw new \InvalidArgumentException('Unsupported node type: '.get_class($node));
        }

        try {
            $nodeBin = static::resolveNodeBinary();
            if ($nodeBin !== null) {
                $nodeDir = dirname($nodeBin);
                $currentPath = getenv('PATH') ?: '';
                if (! str_contains($currentPath, $nodeDir)) {
                    putenv('PATH='.$nodeDir.PATH_SEPARATOR.$currentPath);
                }
            }

            $html = Shiki::highlight(
                code: $code,
                language: $language,
                theme: ['light' => $this->lightTheme, 'dark' => $this->darkTheme],
            );

            $attrs = '';

            if ($language !== 'text') {
                $attrs .= ' data-language="'.$language.'"';
            }

            if ($this->showLineNumbers) {
                $attrs .= ' data-line-numbers';
            }

            if ($attrs !== '') {
                $html = preg_replace('/^<pre\s/', '<pre'.$attrs.' ', $html) ?? $html;
            }

            return $html;
        } catch (\Throwable $e) {
            $escaped = htmlspecialchars($code, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $attrs = '';
            if ($language !== 'text') {
                $attrs .= ' data-language="'.$language.'"';
            }
            if ($this->showLineNumbers) {
                $attrs .= ' data-line-numbers';
            }

            return sprintf(
                '<pre class="shiki-fallback"%s><code>%s</code></pre>',
                $attrs,
                $escaped,
            );
        }
    }
}
