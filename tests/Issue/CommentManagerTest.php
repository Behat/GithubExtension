<?php

namespace Issue;

use Behat\GithubExtension\Issue\CommentManager;

class CommentManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider getValidCommentData
     */
    public function should_send_comment_to_github($user, $repository, $issueNumber, $comment, $expectedCallNbToGithubApi)
    {
        $commentManager = $this->getCommentManager($user, $repository, $comment, $expectedCallNbToGithubApi);

        $commentManager->handle($issueNumber);
    }

    /**
     * @test
     * @dataProvider getInvalidCommentData
     * @expectedException InvalidArgumentException
     */
    public function should_throw_an_exception_if_comment_is_empty($user, $repository, $issueNumber, $comment, $expectedCallNbToGithubApi)
    {
        $commentManager = $this->getCommentManager($user, $repository, $comment, $expectedCallNbToGithubApi);

        $commentManager->handle($issueNumber);
    }

    public static function getValidCommentData()
    {
        return array(
            array('Behat', 'GithubExtension', 1, 'success', 1),
            array('Behat', 'GithubExtension', 2, 'success', 1),
        );
    }

    public static function getInvalidCommentData()
    {
        return array(
            array('Behat', 'Behat', 1, '', 0),
            array('Behat', 'Behat', 1, null, 0),
        );
    }

    private function getCommentManager($user, $repository, $comment, $expectedCallNbToGithubApi)
    {
        return new CommentManager(
            $this->getIssueDataCollector(),
            $this->getApiClient($expectedCallNbToGithubApi),
            $this->getTwigGenerator($comment),
            $user,
            $repository
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

    private function getIssueDataCollector()
    {
        $mock = $this->getMockBuilder('Behat\GithubExtension\DataCollector\IssueDataCollector')
            ->disableOriginalConstructor()
            ->setMethods(array('getScenarioResult'))
            ->getMock()
        ;

        $mock
            ->expects($this->once())
            ->method('getScenarioResult')
            ->will($this->returnValue(array()))
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
}
