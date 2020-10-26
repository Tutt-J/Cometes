<?php

namespace App\Service;

use App\Entity\Purchase;
use App\Entity\PurchaseContent;
use App\Entity\Content;
use Doctrine\ORM\EntityManagerInterface;
use http\Exception\InvalidArgumentException;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

/**
 * Class ContentOnlineAdministrator
 * @package App\Service
 */
class UserContentAdministrator
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /** KernelInterface $appKernel */
    private KernelInterface $appKernel;

    /**
     * @var object|string
     */
    private $user;

    /**
     * @var Security
     */
    private Security $security;

    /**
     * @var YouTubeDownloader
     */
    private YouTubeDownloader $youtubeDownloader;

    /**
     * ContentOnlineAdministrator constructor.
     * @param EntityManagerInterface $em
     * @param Security $security
     * @param TokenStorageInterface $tokenStorage
     * @param KernelInterface $appKernel
     * @param YouTubeDownloader $youTubeDownloader
     */
    public function __construct(
        EntityManagerInterface $em,
        Security $security,
        TokenStorageInterface $tokenStorage,
        KernelInterface $appKernel,
        YouTubeDownloader $youTubeDownloader
    ) {
        $this->em = $em;
        $this->user = $tokenStorage->getToken()->getUser();
        $this->appKernel = $appKernel;
        $this->security=$security;
        $this->youtubeDownloader=$youTubeDownloader;
    }

    /**
     * @param $content
     * @param $listContent
     * @return mixed
     */
    public function setArrayContent($content, $listContent)
    {
        if (!array_key_exists($content->getType()->getSlug().'s', $listContent)) {
            $listContent[$content->getType()->getSlug().'s']=[$content];
        } else {
            if (!in_array($content, $listContent[$content->getType()->getSlug().'s'])) {
                array_push($listContent[$content->getType()->getSlug().'s'], $content);
            }
        }
        return $listContent;
    }

    /**
     * @return array
     */
    public function getUserContents()
    {
        $purchases=$this->em
            ->getRepository(Purchase::class)
            ->findBy(
                array(
                    'user' => $this->user,
                )
            );

        $contents=[];

        foreach ($purchases as $purchase) {
            foreach ($purchase->getPurchaseContent() as $content) {
                array_push($contents, $content);
            }
        }

        return $contents;
    }

    /**
     * @param $content
     * @return object[]
     */
    public function getPackContents($content)
    {
        return $this->em
            ->getRepository(Content::class)
            ->findBy(
                array(
                    'type' => $content->getType(),
                    'isPack' => false,
                    'pack' => $content
                ),
            );
    }


    /**
     * @param $id
     */
    public function download($id)
    {
        $handler = $this->youtubeDownloader;
        // Youtube video url
        $youtubeURL = 'https://www.youtube.com/watch?v='.$id;

        // Check whether the url is valid
        if (!empty($youtubeURL) && !filter_var($youtubeURL, FILTER_VALIDATE_URL) === false) {
            // Get the downloader object
            $downloader = $handler->getDownloader($youtubeURL);

            // Set the url
            $downloader->setUrl($youtubeURL);

            // Validate the youtube video url
            if ($downloader->hasVideo()) {
                // Get the video download link info
                $videoDownloadLink = $this->getVideoDownloadLink($downloader);
                $videoTitle = $videoDownloadLink['title'];
                $videoFormat = $videoDownloadLink['format'];
                $downloadURL = $videoDownloadLink['url'];
                $fileName = strtolower(str_replace(' ', '_', $videoTitle)).'.'.$videoFormat;

                if (!empty($downloadURL)) {
                    // Define header for force download
                    header("Cache-Control: public");
                    header("Content-Description: File Transfer");
                    header("Content-Disposition: attachment; filename=$fileName");
                    header("Content-Type: video/mp4");
                    header("Content-Transfer-Encoding: binary");

                    // Read the file
                    readfile($downloadURL);
                }
            } else {
                throw new NotFoundHttpException('La vidéo n\'existe pas.');
            }
        } else {
            throw new InvalidArgumentException('L\'url de la vidéo est invalide.');
        }
    }

    /**
     * @param bool $downloader
     * @return mixed
     */
    public function getVideoDownloadLink(bool $downloader)
    {
        $videoDownloadLink = $downloader->getVideoDownloadLink();
        foreach ($videoDownloadLink as $video) {
            if ($video['quality'] == "hd720") {
                $videoDownloadLink = $video;
                break;
            }
        }
        return $videoDownloadLink;
    }
}
