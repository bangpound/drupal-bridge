<?php

namespace Drufony\Bridge\Twig\Node;

/**
 * Class EntityViewNode.
 */
class EntityViewNode extends \Twig_Node
{
    /**
     * @param array               $entityType
     * @param \Twig_NodeInterface $ids
     * @param string              $viewMode
     * @param null                $langcode
     * @param null                $page
     * @param int                 $lineno
     * @param null                $tag
     */
    public function __construct($entityType, \Twig_NodeInterface $ids, $viewMode = 'full', $langcode = null, $page = null, $lineno = 0, $tag = null)
    {
        $nodes = array();

        // Load the entities by type and an array of IDs.

        // Assemble arguments for entity_load().
        $argumentsNodes = array();
        $argumentsNodes[] = $entityTypeNode = new \Twig_Node_Expression_Constant($entityType, $lineno);
        $argumentsNodes[] = $ids;

        // Construct call to entity_load() and capture the result in temporary
        // variable named `entities`.
        $valuesNode = new \Twig_Node(array(new \Twig_Node_Expression_Function('entity_load', new \Twig_Node($argumentsNodes), $lineno)));
        $entitiesNamesNode = new \Twig_Node(array(new \Twig_Node_Expression_TempName('entities', $lineno)));
        $nodes[] = new \Twig_Node_Set(false, $entitiesNamesNode, $valuesNode, $lineno);

        // Assemble arguments for entity_view()
        $argumentsNodes = array();
        $argumentsNodes[] = $entityTypeNode;
        $argumentsNodes[] = $entitiesNamesNode;
        $argumentsNodes[] = new \Twig_Node_Expression_Constant($viewMode, $lineno);
        $argumentsNodes[] = new \Twig_Node_Expression_Constant($langcode, $lineno);
        $argumentsNodes[] = new \Twig_Node_Expression_Constant($page, $lineno);

        // Construct call to entity_view() and capture the result in
        // temporary variable named `output`.
        $valuesNode = new \Twig_Node(array(new \Twig_Node_Expression_Function('entity_view', new \Twig_Node($argumentsNodes), $lineno)));
        $outputNamesNode = new \Twig_Node(array(new \Twig_Node_Expression_TempName('output', $lineno)));
        $nodes[] = new \Twig_Node_Set(false, $outputNamesNode, $valuesNode, $lineno);

        // Assemble arguments for drupal_render().
        $argumentsNodes = array();
        $argumentsNodes[] = $outputNamesNode;

        // Construct call to drupal_render() and echo the result.
        $expr = new \Twig_Node_Expression_Function('drupal_render', new \Twig_Node($argumentsNodes), $lineno);
        $nodes[] = new \Twig_Node_Print($expr, $lineno);

        // Pass the original arguments up.
        $attributes = array(
          'entity_type' => $entityType,
          'view_mode' => $viewMode,
          'langcode' => $langcode,
          'page' => $page,
        );

        parent::__construct($nodes, $attributes, $lineno, $tag);
    }
}
