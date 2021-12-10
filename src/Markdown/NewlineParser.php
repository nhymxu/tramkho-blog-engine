<?php

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (https://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Markdown;

use League\CommonMark\Node\Inline\Newline;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;
use League\CommonMark\Parser\Inline\InlineParserInterface;

final class NewlineParser implements InlineParserInterface
{
    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::regex('\\n');
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $inlineContext->getCursor()->advanceBy(1);

        // Check previous inline for trailing spaces
        $spaces     = 0;
        $lastInline = $inlineContext->getContainer()->lastChild();
        if ($lastInline instanceof Text) {
            $trimmed = \rtrim($lastInline->getLiteral(), ' ');
            $spaces  = \strlen($lastInline->getLiteral()) - \strlen($trimmed);
            if ($spaces) {
                $lastInline->setLiteral($trimmed);
            }
        }

        $inlineContext->getContainer()->appendChild(new Newline(Newline::HARDBREAK));

        return true;
    }
}
