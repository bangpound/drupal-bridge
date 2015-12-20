<?php

namespace Drufony\Bridge\Twig\TokenParser;

use Drufony\Bridge\Twig\Node\EntityViewNode;

class EntityViewTokenParser extends \Twig_TokenParser
{
    public function parse(\Twig_Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        // Use entity info to validate tokens and let us use names instead of
        // strings.
        $info = entity_get_info();

        // Entity type name
        $entityType = $stream->expect(\Twig_Token::NAME_TYPE, array_keys($info))->getValue();

        // IDs of entities to view.
        $ids = $parser->getExpressionParser()->parseArrayExpression();

        // Optional view mode token.
        $viewModeToken = $stream->nextIf(\Twig_Token::NAME_TYPE, array_keys($info[$entityType]['view modes']));
        $viewMode = isset($viewModeToken) ? $viewModeToken->getValue() : 'full';

        // Optional langcode token.
        $langcodeToken = $stream->nextIf(\Twig_Token::NAME_TYPE, array_diff(field_content_languages(), array('und')));
        $langcode = isset($langcodeToken) ? $langcodeToken->getValue() : null;

        // Optional page flag token.
        $pageToken = $stream->nextIf(\Twig_Token::NAME_TYPE, 'page');
        $page = isset($pageToken) ? $pageToken->getValue() : null;

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new EntityViewNode($entityType, $ids, $viewMode, $langcode, $page, $token->getLine(), $this->getTag());
    }

    public function getTag()
    {
        return 'entity_view';
    }
}
