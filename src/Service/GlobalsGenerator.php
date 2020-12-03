<?php

namespace App\Service;

use App\Entity\Opinion;
use App\Entity\Purchase;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\GuzzleException;
use Instagram\Api;
use Instagram\Exception\InstagramAuthException;
use Instagram\Exception\InstagramException;
use Instagram\Exception\InstagramFetchException;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;

/**
 * Class SocialGenerator
 * @package App\Service
 */
class GlobalsGenerator
{

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * BasketAdministrator constructor.
     * @param EntityManagerInterface $em
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->logger=$logger;
    }

    /**
     * @return array
     * @throws GuzzleException
     */
    public function getLastInstagramPost()
    {
        $endpoint  = $_ENV['BEHOLD_URL'];
        $curl = curl_init($endpoint);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

        $data = curl_exec($curl);
        return json_decode($data);
    }

    /**
     * @return object[]
     */
    public function getOpinions()
    {
        return $this->em
            ->getRepository(Opinion::class)
            ->findBy(
                ['isOnline' => 1],
                ['id' => 'DESC']
            );
    }

    public function getAmountMonth($month){
        $result=$this->em
            ->getRepository(Purchase::class)
            ->findByMonth($month);
        return $result[0]['amount'];
    }
}
