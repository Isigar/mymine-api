<?php


namespace App\Core\Firebase\Db;


use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Exception\ApiException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class Database
{
    private $firebase;
    private $db;

    public function __construct()
    {
        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__."/../credentials/mymine.json");
        $firebase = (new Factory())
            ->withServiceAccount($serviceAccount)
            ->create();
        $db = $firebase->getDatabase();
        $this->db = $db;
        $this->firebase = $firebase;
    }

    /**
     * @param string $reference
     * @return mixed
     * @throws ApiException
     */
    public function getValues(string $reference){
        return $this->db->getReference($reference)->getValue();
    }

    /**
     * @param string $reference
     * @return Reference
     * @throws ApiException
     */
    public function remove(string $reference){
        return $this->db->getReference($reference)->remove();
    }

    /**
     * @param string $reference
     * @param mixed $data
     * @return Reference
     * @throws ApiException
     */
    public function save(string $reference,$data){
        return $this->db->getReference($reference)->set($data);
    }

    /**
     * @param string $reference
     * @param $data
     * @return Reference
     * @throws ApiException
     */
    public function push(string $reference, $data){
        return $this->db->getReference($reference)->push($data);
    }

    /**
     * @param string $reference
     * @param $data
     * @return Reference
     * @throws ApiException
     */
    public function update(string $reference, $data){
        return $this->db->getReference($reference)->update($data);
    }

    /**
     * @return \Kreait\Firebase
     */
    public function getFirebase(): \Kreait\Firebase
    {
        return $this->firebase;
    }

    /**
     * @return \Kreait\Firebase\Database
     */
    public function getDb(): \Kreait\Firebase\Database
    {
        return $this->db;
    }


}