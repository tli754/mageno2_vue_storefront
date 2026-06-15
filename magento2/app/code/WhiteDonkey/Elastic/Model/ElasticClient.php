<?php

/**
 * Created by IntelliJ IDEA.
 * User: tao
 * Date: 3/07/20
 * Time: 11:41 AM
 */
namespace WhiteDonkey\Elastic\Model;

use Elasticsearch\ClientBuilder;

class ElasticClient
{
    protected $_client;
    protected $_host = 'http://elasticsearch:9200';

    public function getClient()
    {
        if (!$this->_client) {
            $this->_client = ClientBuilder::create()
                ->setHosts([$this->_host])
                ->build();
        }
        return $this->_client;
    }

    public function upsertData(array $data, int $storeId, string $writeAlias, string $indexType)
    {
        if (!$data) {
            return;
        }

        $params = ['body' => array_reduce($data, function($_carry, $_item) use ($storeId, $writeAlias, $indexType) {
            $json = json_encode($_item, JSON_UNESCAPED_UNICODE);
            if (!$json) {
                array_walk_recursive($_item, function(&$val, $key){
                    if(!mb_detect_encoding($val, 'utf-8', true)){
                        $val = utf8_encode($val);
                    }
                });
                $json = json_encode($_item, JSON_UNESCAPED_UNICODE);
            }
            if (!$json) {
                new \Exception("Missing item data during reindex");
            }
//                $_item = $this->_utf8Encode($_item);
            $_carry .= '{"update": {"_id": "'.($_item['storeId']??$storeId).':'.$_item['magentoId'].'", "_index": "'.$writeAlias.'", "_type":"'.$indexType.'"}}'
                ."\n".'{"doc": '.$json.', "doc_as_upsert": true}'."\n";
            return $_carry;
        }, '')];
        $this->getClient()->bulk($params);
    }

    public function deleteOthers(array $ids, string $writeAlias, string $indexType)
    {
        $this->getClient()->deleteByQuery([
            'index' => $writeAlias,
            'type' => $indexType,
            'body' => [
                'query' => [
                    'bool' => [
                        'must_not' => ['terms' => ['id' => $ids]],
                    ]
                ]
            ]
        ]);

    }

}
