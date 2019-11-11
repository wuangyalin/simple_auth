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
     * @Route("/googlelogin", name="googlelogin")
     */
    public function googleloginAction() 
    {
        $client= new \Google_Client();
        $client->setApplicationName('Simple Auth');// to set app name
        $credentials_path = $this->getParameter('google_credentialspath');
        $client->setAuthConfig($credentials_path);

        $client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/redirect');

        // $client->setScopes(\Google_Service_Drive::DRIVE_METADATA_READONLY);
        // $client->setAccessType('offline');        // offline access
        // $client->setIncludeGrantedScopes(true);   // incremental auth

        $client->addScope('profile');
        $client->addScope('email');
        $client->addScope(\Google_Service_Drive::DRIVE_METADATA_READONLY);

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
        $credentials_path = $this->getParameter('google_credentialspath');
        $client->setAuthConfig($credentials_path);
        $client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/redirect');
        $service = new \Google_Service_Oauth2($client);
        $code=$client->authenticate($_GET['code']);// to get code
        $access_token = $client->getAccessToken();
        $client->setAccessToken($access_token);// to get access token by setting of $code

        /**
         * get google drive file
         */
        // $drive = new \Google_Service_Drive($client);
        // $files_list = $drive->files->listFiles(array())->getFiles();
        // if (count($files_list) == 0) {
        //     print "No files found.\n";
        // } else {
        //     foreach ($files_list as $file) {
        //         $res['name'] = $file->getName();
        //         $res['id'] = $file->getId();
        //         $files[] = $res;
        //     }
        //     dump($files);
        // }
        
        // die();

        $userDetails=$service->userinfo->get();// to get user detail by using access token

        $familyName = $userDetails->familyName;
        $fullname = $userDetails->name;

        $user_picture = $userDetails->picture;
        $email = $userDetails->email;
        $google_id = $userDetails->id;
        $user = new user;
        if($familyName){
            $user->setUsername($familyName.'-'.$google_id);
        }else{
            $user->setUsername($google_id);
        }
        if($fullname){
            $user->setFullName($fullname);
        }else{
            $user->setFullName($user->getUsername());
        }
        $user->setgoogleplus_picture($user_picture);
        $user->setGoogleplusId($google_id);
        $user->setEmail($email);
        $user->addRole('ROLE_ADMIN');
        $user->setPlainPassword('simpe-auth');
        $user->setEnabled(1);


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