<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\user;

class LoginController extends Controller
{
   /**
     * @Route("/login", name="login")
     */
    public function loginAction()
    {
        $client= new \Google_Client();
        $client->setApplicationName('Simple Auth');// to set app name
        $client->setClientId('303810699327-8p0ve2rmd2toohrild5db4q40ugbt9jl.apps.googleusercontent.com');// to set app id or client id
        $client->setClientSecret('hVWLZzCzUzuvzAk3TGjGH4cU');// to set app secret or client secret
        $client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/redirect');
        $client->addScope('email');

        $url= $client->createAuthUrl();// to get login url
        // echo '<a href="' . $url . '">Log in with Google!</a>';
        return $this->redirect($url);
    }

    /**
     * @Route("/redirect", name="redirect")
     */
    public function redirectAction()
    {
        $client= new \Google_Client();
        $client->setApplicationName('Simple Auth');// to set app name
        $client->setClientId('303810699327-8p0ve2rmd2toohrild5db4q40ugbt9jl.apps.googleusercontent.com');// to set app id or client id
        $client->setClientSecret('hVWLZzCzUzuvzAk3TGjGH4cU');// to set app secret or client secret
        $client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/redirect');
        $service = new \Google_Service_Oauth2($client);
        $code=$client->authenticate($_GET['code']);// to get code
        $client->setAccessToken($code);// to get access token by setting of $code
        $userDetails=$service->userinfo->get();// to get user detail by using access token
        $username = $userDetails->name;
        $email = $userDetails->email;
        $createDate = new\DateTime('now');
        $user = new user;
        if($username){
            $user->setUsername($username);
        }
        $user->setEmail($email);
        $userrole = 'user';
        $user->setUserrole($userrole);
        $user->setCreatedate($createDate);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        $this->addFlash(
                'notice',
                'user added'
        );
        return $this->redirectToRoute('crud_list');
    }
}