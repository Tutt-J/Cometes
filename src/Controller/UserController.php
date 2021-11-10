<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Purchase;
use App\Entity\PurchaseContent;
use App\Entity\Content;
use App\Entity\ResetPasswordRequest;
use App\Entity\UserEvent;
use App\Form\ChangePasswordType;
use App\Form\UserUpdateType;
use App\Service\UserContentAdministrator;
use App\Service\UsersHelper;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class UserController
 *
 * @IsGranted("ROLE_USER")
 *
 * @package App\Controller
 */
class UserController extends AbstractController
{

    /**
     * @Route("/mon-compte/ma-magie-en-ligne/video/télécharger/{id}", name="app_video_download")
     *
     * @param $id
     * @param UserContentAdministrator $userContentAdministrator
     * @return void
     */
    public function videoDownload($id, UserContentAdministrator $userContentAdministrator)
    {
        return $userContentAdministrator->download($id);
    }

    /**
     * @Route("/mon-compte/ma-magie-en-ligne/ebook/{path}", name="app_ebook")
     *
     * @param $path
     * @param UserContentAdministrator $userContentAdministrator
     * @return BinaryFileResponse
     */
    public function ebook($path, UserContentAdministrator $userContentAdministrator)
    {
        return $userContentAdministrator->generateFileResponse('application/pdf', 'ebooks', $path, ['.pdf']);
    }

    /**
     * @Route("/mon-compte/mes-commandes/{path}/{id}", name="app_invoice")
     *
     * @param $path
     * @param UserContentAdministrator $userContentAdministrator
     * @param Purchase $purchase
     * @return BinaryFileResponse
     */
    public function invoice($path, UserContentAdministrator $userContentAdministrator, Purchase $purchase)
    {
        return $userContentAdministrator->generateInvoice('src/invoices', $path,  $purchase);
    }


    /**
     * @Route("/mon-compte", name="app_account")
     *
     * @return Response
     */
    public function profile()
    {
        return $this->render(
            'security/profile.html.twig'
        );
    }


    /**
     * @Route("/mon-compte/ma-magie-en-ligne", name="app_account_online_content")
     *
     * @param UserContentAdministrator $userContentAdministrator
     * @return Response
     */
    public function contentsOnline(UserContentAdministrator $userContentAdministrator)
    {
        $listContent = [];

        $contents= $userContentAdministrator->getUserContents();

        foreach ($contents as $content) {
            $content=$content->getContent();
            if ($content->getIsPack()) {
                $contentsPack= $userContentAdministrator->getPackContents($content);
                foreach ($contentsPack as $oneContent) {
                    $listContent=$userContentAdministrator->setArrayContent($oneContent, $listContent);
                }
            } else {
                $listContent=$userContentAdministrator->setArrayContent($content, $listContent);
            }
        }

        if(array_key_exists('initiations', $listContent)){
            $listContent['videos']=array_merge($listContent['videos'],$listContent['initiations']);

        }

        return $this->render(
            'security/online_contents.html.twig',
            [
            'contents' => $listContent
            ]
        );
    }

    /**
     * @Route("/mon-compte/mes-prochains-evenements", name="app_account_events")
     *
     * @return Response
     * @throws Exception
     */
    public function events()
    {
        //get tous les users event où user
        $userEvents = $this->getDoctrine()
            ->getRepository(UserEvent::class)
            ->findBy(
                ['user' => $this->getUser()]
            );

        $listEvents=[
            'passed'=>[],
            'toBecome'=>[]
        ];
        foreach ($userEvents as $event) {
            if (($event->getEvent()->getStartDate())->format('Y/m/d') < (new DateTime())->format('Y/m/d')) {
                array_push($listEvents['passed'], $event);
            } else {
                array_push($listEvents['toBecome'], $event);
            }
        }


        //si c'est passé dans array passé
        //sinon dans à venir
        return $this->render(
            'security/events.html.twig',
            [
                'events' => $listEvents
            ]
        );
    }

    /**
     * @Route("/mon-compte/editer-mes-informations-personnelles", methods="GET|POST", name="app_account_edit")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function edit(Request $request): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(UserUpdateType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Votre compte a bien été mis à jour');

            return $this->redirectToRoute('app_account');
        }

        return $this->render(
            'security/profile_edit.html.twig',
            [
            'form' => $form->createView(),
            ]
        );
    }


    /**
     * @Route("/mon-compte/changer-mon-mot-de-passe", methods="GET|POST", name="user_change_password")
     *
     * @param Request $request
     * @param UsersHelper $usersHelper
     * @return Response
     */
    public function changePassword(Request $request, UsersHelper $usersHelper): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $usersHelper->reset($form, $user, 'app_account');
        }

        return $this->render(
            'security/change_password.html.twig',
            [
            'form' => $form->createView(),
            ]
        );
    }


    /**
     * @Route("/mon-compte/mes-commandes", methods="GET|POST", name="user_purchases")
     *
     * @return Response
     */
    public function purchase()
    {
        $purchases= $this->getDoctrine()
            ->getRepository(Purchase::class)
            ->findBy(
                array('user' => $this->getUser()),
                array('createdAt' => "DESC")
            );

        $purchasesArray=[];
        foreach ($purchases as $purchase) {
            $arrayContent=[];

            //Event
            $userEvents=$this->getDoctrine()
                ->getRepository(UserEvent::class)
                ->findOneBy([
                    'purchase' => $purchase
                ]);


            if (!is_null($userEvents)) {
                $event=[
                    'entity' => $userEvents->getEvent(),
                    'price' => $userEvents->getPurchase()->getAmount(),
                    'path' => 'singleEvent'
                ];

                array_push($arrayContent, $event);
            }



            //contents
            $purchaseContents = $this->getDoctrine()
                ->getRepository(PurchaseContent::class)
                ->findBy(
                    ['purchase' => $purchase]
                );
            foreach ($purchaseContents as $purchaseContent) {
                $content = $this->getDoctrine()
                    ->getRepository(Content::class)
                    ->findOneBy(
                        ['id' => $purchaseContent->getContent()]
                    );


                $content=[
                    'entity' => $content,
                    'price' => $purchaseContent->getPrice(),
                    'path' => $content->getType()->getSlug().'Online'
                ];
                array_push($arrayContent, $content);
            }
            $array=[
                'purchase' => $purchase,
                'contents' => $arrayContent
            ];
            array_push($purchasesArray, $array);
        }



        return $this->render(
            'security/purchases.html.twig',
            [
            'purchases' => $purchasesArray,
            ]
        );
    }


    /**
     * @Route("/mon-compte/supprimer-mon-compte", name="user_delete")
     *
     * @param UsersHelper $usersHelper
     * @return RedirectResponse|Response
     */
    public function delete(UsersHelper $usersHelper)
    {
        if (isset($_POST['delete_confirm'])) {
            $usersHelper->delete($this->getUser());
            $this->addFlash('success', 'Votre compte a été désactivé et vos données ont été anonymisées.');
            return $this->redirectToRoute('app_logout');
        }


        return $this->render('security/delete.html.twig');
    }
}
