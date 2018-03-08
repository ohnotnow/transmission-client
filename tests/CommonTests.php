<?php

namespace Tests;

trait CommonTests
{
    /** @test */
    public function we_can_add_a_valid_torrent()
    {
        $client = $this->getClient();

        $torrent = $client->addPaused(__DIR__ . '/data/asimov_foundation_archive_org.torrent');

        $this->assertNotNull($torrent);
        $this->assertEquals("IsaacAsimovFoundation6Of864kb", $torrent->name);

        $client->remove($torrent->id);
    }

    /** @test */
    public function adding_an_invalid_torrent_throws_an_invalid_argument_exception()
    {
        $client = $this->getClient();

        try {
            $torrent = $client->addPaused('not-a.torrent');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('invalid or corrupt torrent file', $e->getMessage());
            return true;
        }

        $this->fail('Expected an InvalidArgumentException but none was thrown');
    }

    /** @test */
    public function adding_the_same_torrent_twice_returns_a_copy_of_the_original_torrent_entry()
    {
        $client = $this->getClient();

        $firstTry = $client->addPaused(__DIR__ . '/data/asimov_foundation_archive_org.torrent');
        $secondTry = $client->addPaused(__DIR__ . '/data/asimov_foundation_archive_org.torrent');

        $client->remove($firstTry->id);
        $this->assertEquals($firstTry->id, $secondTry->id);
        $this->assertEquals($firstTry->name, $secondTry->name);
    }

    /** @test */
    public function can_get_a_list_of_all_torrents()
    {
        $client = $this->getClient();

        sleep(1); // slow transmission api manual delay :-/
        $originalTorrents = $client->all();

        $torrent = $client->addPaused(__DIR__ . '/data/asimov_foundation_archive_org.torrent');

        $torrents = $client->all();

        $client->remove($torrent->id);
        $this->assertEquals(count($originalTorrents) + 1, count($torrents));
    }

    /** @test */
    public function can_get_a_single_torrent()
    {
        $client = $this->getClient();
        sleep(1); // slow transmission api manual delay :-/
        $torrent1 = $client->addPaused(__DIR__ . '/data/asimov_foundation_archive_org.torrent');

        $torrent = $client->find($torrent1->id);

        $client->remove($torrent->id);
        $this->assertArraySubset(
            [
              "doneDate" => 0,
              "eta" => -1,
              "haveValid" => 0,
              "id" => $torrent1->id,
              "name" => "IsaacAsimovFoundation6Of864kb",
              "rateDownload" => 0,
              "rateUpload" => 0,
              "status" => 2,
              "totalSize" => 364514248,
              "percentDone" => 0,
            ],
            $torrent->toArray()
        );
        $this->assertArrayHasKey('downloadDir', $torrent->toArray());
    }

    /** @test */
    public function trying_to_find_a_torrent_which_doesnt_exist_returns_null()
    {
        $client = $this->getClient();

        $torrent = $client->find(1234);

        $this->assertNull($torrent);
    }

    /** @test */
    public function we_can_ask_for_a_non_existent_torrent_to_throw_an_exception()
    {
        $client = $this->getClient();

        try {
            $torrent = $client->findOrFail(1234);
        } catch (\RuntimeException $e) {
            $this->assertEquals('No such torrent', $e->getMessage());
            return true;
        }

        $this->fail("An exception was expected, but wasn't thrown");
    }
}
