<?php

namespace Behat\GithubExtension\Issue;

class TwigGenerator implements GeneratorInterface
{
    private $viewPath;

    public function __construct($viewPath)
    {
        $this->viewPath = $viewPath;
    }

    public function render(array $result)
    {
        $loader = new \Twig_Loader_Filesystem(__DIR__.'/../'.$this->viewPath);
        $twig   = new \Twig_Environment($loader, array());

        return $twig->render('result.md.twig', array(
            'run_date' => new \DateTime(),
            'results'  => $result,
        ));
    }
}
