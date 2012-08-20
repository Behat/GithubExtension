<?php

namespace Issue;

use Behat\GithubExtension\Issue\CommentManager;
use Behat\Gherkin\Node\FeatureNode;

class CommentManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider getValidCommentData
     */
    public function should_send_comment_to_github($comment, $expectedCallNbToGithubApi)
    {
        $commentManager = $this->getCommentManager($comment, $expectedCallNbToGithubApi);

        $commentManager->handle($this->getFeatureNode(), array('scenarios' => array(), 'feature' => null));
    }

    /**
     * @test
     * @dataProvider getInvalidCommentData
     * @expectedException InvalidArgumentException
     */
    public function should_throw_an_exception_if_comment_is_empty($comment, $expectedCallNbToGithubApi)
    {
        $commentManager = $this->getCommentManager($comment, $expectedCallNbToGithubApi);

        $commentManager->handle($this->getFeatureNode(), array('scenarios' => array(), 'feature' => null));
    }

    public static function getValidCommentData()
    {
        return array(
            array('success', 1),
            array('success', 1),
        );
    }

    public static function getInvalidCommentData()
    {
        return array(
            array('', 0),
            array(null, 0),
        );
    }

    private function getCommentManager($comment, $expectedCallNbToGithubApi)
    {
        return new CommentManager(
            $this->getUrlExtractor(),
            $this->getApiClient($expectedCallNbToGithubApi),
            $this->getTwigGenerator($comment)
        );
    }

    private function getTwigGenerator($comment)
    {
        $mock = $this->getMockBuilder('Behat\GithubExtension\Issue\TwigGenerator')
            ->disableOriginalConstructor()
            ->setMethods(array('render'))
            ->getMock()
        ;

        $mock
            ->expects($this->once())
            ->method('render')
            ->will($this->returnValue($comment))
        ;

        return $mock;
    }

    private function getUrlExtractor()
    {
        $mock = $this->getMockBuilder('Behat\GithubExtension\Issue\UrlExtractor')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        return $mock;
    }

    private function getApiClient($expectedCallNbToGithubApi)
    {
        $mock = $this->getMockBuilder('Github\Client')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $mock
            ->expects($this->any())
            ->method('api')
            ->with('issue')
            ->will($this->returnValue($this->getApiIssue($expectedCallNbToGithubApi)))
        ;

        return $mock;
    }

    private function getApiIssue($expectedCallNbToGithubApi)
    {
        $mock = $this->getMockBuilder('Github\Api\Issue')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $mock
            ->expects($this->any())
            ->method('comments')
            ->will($this->returnValue($this->getApiComments($expectedCallNbToGithubApi)))
        ;

        return $mock;
    }

    private function getApiComments($expectedCallNbToGithubApi)
    {
        $mock = $this->getMockBuilder('Github\Api\Issue\Comments')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $mock
            ->expects($this->exactly($expectedCallNbToGithubApi))
            ->method('create')
        ;

        return $mock;
    }

    private function getFeatureNode()
    {
        $mock = $this->getMockBuilder('Behat\Gherkin\Node\FeatureNode')
            ->getMock();

        return $mock;
    }
}
