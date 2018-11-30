<?php
/**
 * Created by PhpStorm.
 * User: Sascha
 * Date: 07.09.2018
 * Time: 14:33
 */

namespace Superbox\SyliusDataPortabilityPlugin\Services;

use Sylius\Component\Resource\Repository\RepositoryInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;


class DataAggregator
{
    /**
     *
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(EntityManager $entityManager, ContainerInterface $container)
    {
        $this->entityManager = $entityManager;
        $this->container = $container;
    }

    /**
     * Gathers all data stored in the database linked to the supplied email and saves it in CSV format
     * @param string $email
     */
    public function collectData(string $email)
    {
        $repositoryNames = array(
            'customer',
            'order',
            'address',
            'address_log_entry',
            'shop_user',
            'payment',
            'product_review'
        );

        $repositories = array();
        foreach($repositoryNames as $repositoryName)
        {
            $repositories[$repositoryName] = $this->findRepository($repositoryName);
        }

        $userData = array();
        $userData['customer'] = $repositories['customer']->findBy(['email' => $email]);
        if ($userData['customer']) {
            $userData['address'] = $repositories['address']->findBy(['customer' => $userData['customer']]);
            $userData['addressHistory'] = $repositories['address_log_entry']->findBy(['id' => $userData['address']]);
            $userData['account'] = $repositories['shop_user']->findBy(['customer' => $userData['customer']]);
            $userData['orders'] = $repositories['order']->findBy(['customer' => $userData['customer']]);
            $userData['payments'] = $repositories['payment']->findBy(['order' => $userData['orders']]);
            $userData['reviews'] = $repositories['product_review']->findBy(['author' => $userData['customer']]);

        $whitelist = array(
            'email',
            'firstName',
            'lastName',
            'birthday',
            'gender',
            'phoneNumber',
            'company',
            'countryCode',
            'street',
            'city',
            'postcode',
            'username',
            'title',
            'rating',
            'comment',
        );

        $filteredData = array();

        $fp = fopen($email.'.csv', 'w');
        foreach($userData as $key => $tableData){
            $tableName = array($key);
            fputcsv($fp, $tableName);
            $intersectLine = array();
           foreach($tableData as $objectData){
               $filteredData[$key] = $this->arrayWhitelist(array_filter($this->objectToArray($objectData)), $whitelist);
               $intersectLine = array_intersect($whitelist, array_flip($filteredData[$key]));
            }
            fputcsv($fp, $intersectLine);
           if (isset($filteredData[$key]))  {
            fputcsv($fp, $filteredData[$key]);
           }
        }
        fclose($fp);
        }
    }

    /**
     * Function to shorten finding of repositories
     *
     * @param string $repositoryName
     * @return RepositoryInterface
     */
    private function findRepository(string $repositoryName): RepositoryInterface
    {
        $repositoryName = sprintf('sylius.repository.%s', $repositoryName);

        /** @var \Sylius\Component\Resource\Repository\RepositoryInterface $repository */
        $repository  = $this->container->get($repositoryName);
        return $repository;
    }

    /**
     * Returns only array entries listed in a whitelist
     *
     * @param array $array original array to operate on
     * @param array $whitelist keys you want to keep
     * @return array
     */
    function arrayWhitelist($array, $whitelist) {
        return array_intersect_key(
            $array,
            array_flip($whitelist)
        );
    }

    /**
     * Returns array from object and creates entries without visibility for each key
     *
     * @param $Instance
     * @return array
     */
    function objectToArray ( &$Instance ) {
        $clone = (array) $Instance;
        $rtn = array ();
        $rtn['___SOURCE_KEYS_'] = $clone;

        while ( list ($key, $value) = each ($clone) ) {
            $aux = explode ("\0", $key);
            $newkey = $aux[count($aux)-1];
            $rtn[$newkey] = &$rtn['___SOURCE_KEYS_'][$key];
        }

        return $rtn;
    }

}