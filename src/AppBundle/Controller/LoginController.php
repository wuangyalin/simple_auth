<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

use AppBundle\Entity\user;

class LoginController extends Controller
{
   /**
     * @Route("/googlelogin", name="login")
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
    public function redirectAction(Request $request) 
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
        $google_id = $userDetails->id;
        $user = new user;
        if($username){
            $user->setUsername($username);
            
        }else{
            $user->setUsername($google_id);
        }
        $user->setGoogleplusId($google_id);
        $user->setEmail($email);
        //$user->setUserRole('ROLE_USER');
        $user->setUserRole('ROLE_USER');
        //$user->addRole('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();
        // A single user by its nickname
        $temp_user = $em->getRepository('AppBundle:user')->findOneBy(array('googleplus_id' => $google_id));
        if($temp_user){
            $this->addFlash(
                'notice',
                'user already exists'
            );
            //Handle getting or creating the user entity likely with a posted form
            // The third parameter "main" can change according to the name of your firewall in security.yml
            $token = new UsernamePasswordToken($temp_user, null, 'main', $temp_user->getRoles());
            $this->get('security.token_storage')->setToken($token);

            // If the firewall name is not main, then the set value would be instead:
            // $this->get('session')->set('_security_XXXFIREWALLNAMEXXX', serialize($token));
            $this->get('session')->set('_security_main', serialize($token));
            
            // Fire the login event manually
            $event = new InteractiveLoginEvent($request, $token);
            $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);    
        }else{
            $em->persist($user);
            $em->flush();
            $this->addFlash(
                    'notice',
                    'user added'
            );
            //Handle getting or creating the user entity likely with a posted form
            // The third parameter "main" can change according to the name of your firewall in security.yml
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);

            // If the firewall name is not main, then the set value would be instead:
            // $this->get('session')->set('_security_XXXFIREWALLNAMEXXX', serialize($token));
            $this->get('session')->set('_security_main', serialize($token));
            
            // Fire the login event manually
            $event = new InteractiveLoginEvent($request, $token);
            $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);    
        }

            
        return $this->redirectToRoute('crud_list');

    }
}