<?php

namespace Issue;

use Behat\GithubExtension\Issue\UrlExtractor;

class UrlExtractorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider getUrls
     */
    public function should_get_the_correct_user_from_the_url($url, $user, $repository, $issueNumber)
    {
        $urlExtractor = $this->getUrlExtractor();

        $this->assertEquals($urlExtractor->getUser($url), $user, 'Couldn\'t resolve the correct user from the url');
    }

    /**
     * @test
     * @dataProvider getUrls
     */
    public function should_get_the_correct_repository_from_the_url($url, $user, $repository, $issueNumber)
    {
        $urlExtractor = $this->getUrlExtractor();

        $this->assertEquals($urlExtractor->getRepository($url), $repository, 'Couldn\'t resolve the correct repository from the url');
    }

    /**
     * @test
     * @dataProvider getUrls
     */
    public function should_get_the_correct_issue_number_from_the_url($url, $user, $repository, $issueNumber)
    {
        $urlExtractor = $this->getUrlExtractor();

        $this->assertEquals($urlExtractor->getIssueNumber($url), $issueNumber, 'Couldn\'t resolve the correct issue number from the url');
    }

    public static function getUrls()
    {
        return array(
            array('https://github.com/Behat/GithubExtension/issues/1', 'Behat', 'GithubExtension', '1'),
            array('http://github.com/Behat/GithubExtension/issues/1', 'Behat', 'GithubExtension', '1'),
        );
    }

    private function getUrlExtractor()
    {
        $ue = new UrlExtractor('#^https?://github.com/(.*)/(.*)/issues/(\d+)#');

        return $ue;
    }
}
