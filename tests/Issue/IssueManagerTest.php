<?php

namespace Issue;

use Behat\GithubExtension\Issue\IssueManager;
use Behat\Gherkin\Node\FeatureNode;
use Behat\GithubExtension\Issue\UrlExtractor;

class IssueManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider getFeatureNodes
     */
    public function should_get_github_url_from_feature(FeatureNode $feature, $expectedResult)
    {
        $issueManager = $this->getIssueManager();

        $r = new \ReflectionClass($issueManager);
        $m = $r->getMethod('getIssue');
        $m->setAccessible(true);

        $this->assertEquals($expectedResult, $m->invoke($issueManager, $feature));
    }

    public static function getFeatureNodes()
    {
        $normalFileFeature = new FeatureNode();
        $normalFileFeature->setFile(__DIR__.'/../fixtures/normal_feature.feature');

        $githubFileFeature = new FeatureNode();
        $githubFileFeature->setFile(__DIR__.'/../fixtures/github_related_feature.feature');

        $githubIssueFeature = new FeatureNode();
        $githubIssueFeature->setFile('https://github.com/Behat/GithubExtension/issues/2');

        return array(
            array($normalFileFeature, null),
            array($githubFileFeature, 'https://github.com/Behat/GithubExtension/issues/1'),
            array($githubIssueFeature, 'https://github.com/Behat/GithubExtension/issues/2'),
        );
    }

    private function getIssueManager($expectedCallNbToGithubApi = 0)
    {
        return new IssueManager(
            $this->getApiClient($expectedCallNbToGithubApi),
            $this->getUrlExtractor()
        );
    }

    private function getUrlExtractor()
    {
        $ue = new UrlExtractor('#https?://github.com/(.*)/(.*)/issues/(\d+)#');

        return $ue;
    }

    private function getApiClient($expectedCallNbToGithubApi = 0)
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
