<?php
namespace EngBlogs\MeiliSearch;

use MeiliSearch\Client;

class MeiliSearch {
    private $client;
    private $host;
    private $masterKey;
    private $indexName;

    public function __construct(string $indexName) {
        $this->indexName = $indexName;
        $this->host = getenv('MEILI_HOST_NAME');
        $this->masterKey = getenv('MEILI_MASTER_KEY');
    }

    public function getClient(): Client {
        if ($this->client) {
            return $this->client;
        }

        $this->client = new Client($this->host, $this->masterKey);

        return $this->client;
    }


    public function getIndex() {
        return $this->getClient()->getIndex($this->indexName);
    }

    public function updateIndexSettings() {
        $string = file_get_contents(__DIR__ . '/index-settings.json');
        $settings = json_decode($string, true);

        $this->getIndex()->updateSettings($settings);
    }

    public function createIndex($primaryKey = 'id') {
        try {
            $indexer = $this->getClient()->createIndex($this->indexName, [
                'primaryKey' => $primaryKey
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Index already exists');
        }

        return $this;
    }

    public function addDocuments(array $documents, $returnStatus = false) {
        $updateItem = $this->getIndex()->addDocuments($documents);
        if ($returnStatus) {
            return $this->getIndex()->getUpdateStatus($updateItem['updateId']);
        }
    }

    public function delete($documentsIds) {
        $this->getIndex()->deleteDocument($documentsIds);
    }
}
