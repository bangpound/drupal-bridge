<?php

namespace Drufony\Bridge\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * Class ProfilerController
 * @package Drufony\Bridge\Controller
 */
class ProfilerController
{
    private $profiler;
    private $twig;

    public function __construct(Profiler $profiler = null, \Twig_Environment $twig)
    {
        $this->profiler = $profiler;
        $this->twig = $twig;
    }

    /**
     * Renders the profiler panel for the given token.
     *
     * @param string  $token          The profiler token
     * @param string  $connectionName
     * @param integer $query
     *
     * @return Response A Response instance
     */
    public function explainAction($token, $connectionName, $query)
    {
        $profiler = $this->profiler;
        $profiler->disable();

        $profile = $profiler->loadProfile($token);
        $queries = $profile->getCollector('drupal_db')->getQueries();

        if (!isset($queries[$connectionName][$query])) {
            return new Response('This query does not exist.');
        }

        $query = $queries[$connectionName][$query];

        try {
            $results = db_query('EXPLAIN ' . $query['query'], $query['args'])
                ->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return new Response('This query cannot be explained.');
        }

        return $this->twig->render(__DIR__ .'../Resources/views/Collector/explain.html.twig',
            array(
                'data' => $results,
                'query' => $query,
            )
        );
    }
}
