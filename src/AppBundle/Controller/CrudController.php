<?php

namespace AppBundle\Controller;

use AppBundle\Entity\user;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\RoleEntity;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class CrudController extends Controller
{
    /**
     * @Route("/", name="crud_list")
     */
    public function listAction()
    {

        $list = $this->getDoctrine()
            ->getRepository('AppBundle:user')
            ->findAll();
        // get current user id to disable the current user modify funciton.
        if( $this->container->get( 'security.authorization_checker' )->isGranted( 'IS_AUTHENTICATED_FULLY' ) )
        {
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
            $current_user_id = $user->getId();
        }else{
            //if not, set to -1
            $current_user_id = -1;
        }
        return $this->render('crud/index.html.twig',array(
            'users' => $list,
            'current_user_id' => $current_user_id
        ));
    }

    /**
     * @Route("/crud/create", name="crud_create")
     */
    public function createAction(Request $request)
    {
        $single_user = new user;
        //create form
        $form = $this->createFormBuilder($single_user)
            ->add('username', TextType::class, array(
            'attr' => array(
                'class' => 'form-control', 
                'style' => 'margin-bottom:15px'
                )
            ))
            ->add('fullName', TextType::class, array(
            'attr' => array(
                'class' => 'form-control', 
                'style' => 'margin-bottom:15px'
                )
            ))
            ->add('email', EmailType::class, array(
                'attr' => array(
                    'class' => 'form-control', 
                    'style' => 'margin-bottom:15px'
                    )
                ))
            ->add('plainPassword', PasswordType::class, array(
                'attr' => array(
                    'class' => 'form-control', 
                    'style' => 'margin-bottom:15px'
                    )
                ))
            ->add('roles', ChoiceType::class, array(
                'attr' => array('class' => 'form-control choice', 
                'style' => 'margin-bottom:15px'),
                'choices' => array(
                    'ROLE_ADMIN' => 'ROLE_ADMIN',
                    'ROLE_USER' => 'ROLE_USER',
                    ),
                'choice_attr' => function($val, $key, $index) {
                    return ['class' => 'role_attr'];
                },
                'multiple' => true,
                'required' => true,
                ))
            ->add('Save', SubmitType::class, array(
                'attr' => array(
                    'class' => 'btn btn-primary', 
                    'style' => 'margin-bottom:15px'),
                    'label' => 'Update User'))
            ->getForm();


        $form -> handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $email = $form['email']->getData();
            $password = $form['plainPassword']->getData(); 
            $username = $form['username']->getData();        
            //check unique
            $em = $this->getDoctrine()->getManager();
            //check if exist
            $temp_user_email = $em->getRepository('AppBundle:user')->findOneBy(
                array('email' => $email)
            );
            $temp_user_username = $em->getRepository('AppBundle:user')->findOneBy(
                array('username' => $username)
            );
            if($temp_user_email || $temp_user_username){
                //add message
                $this->addFlash(
                    'notice',
                    'user already exists'
                );
            }else{
                if(!$email){
                    $single_user->setEmail($email);
                }
                if(!$password){
                    $single_user->setPlainPassword($password);
                }
                $single_user->setEnabled(1);
                $em = $this->getDoctrine()->getManager();
                $em->persist($single_user);
                $em->flush();
                $this->addFlash(
                    'notice',
                    'user updated'
                );
            }
            return $this->redirectToRoute('crud_list');
        }
        return $this->render('crud/create.html.twig',array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/crud/edit/{id}", name="crud_edit")
     */
    public function editAction($id,Request $request)
    {
        $roles = $this->getParameter('security.role_hierarchy.roles');

        $single_user = $this->getDoctrine()
        ->getRepository('AppBundle:user')
        ->find($id);

        //create form
        $form = $this->createFormBuilder($single_user)
            ->add('username', TextType::class, array(
            'attr' => array(
                'class' => 'form-control', 
                'style' => 'margin-bottom:15px'
                )
            ))
            ->add('fullName', TextType::class, array(
            'attr' => array(
                'class' => 'form-control', 
                'style' => 'margin-bottom:15px'
                )
            ))
            ->add('email', EmailType::class, array(
                'attr' => array(
                    'class' => 'form-control', 
                    'style' => 'margin-bottom:15px'
                    )
                ))
            ->add('plainPassword', PasswordType::class, array(
                'attr' => array(
                    'class' => 'form-control', 
                    'style' => 'margin-bottom:15px'
                    )
                ))
            ->add('roles', ChoiceType::class, array(
                'attr' => array('class' => 'form-control choice', 
                'style' => 'margin-bottom:15px'),
                'choices' => array(
                    'ROLE_ADMIN' => 'ROLE_ADMIN',
                    'ROLE_USER' => 'ROLE_USER',
                    ),
                'choice_attr' => function($val, $key, $index) {
                    return ['class' => 'role_attr'];
                },
                'multiple' => true,
                'required' => true,
                ))
            ->add('Save', SubmitType::class, array(
                'attr' => array(
                    'class' => 'btn btn-primary', 
                    'style' => 'margin-bottom:15px'),
                    'label' => 'Update User'))
            ->getForm();
        
        $form -> handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $email = $form['email']->getData();
            $password = $form['plainPassword']->getData();
            if(!$email){
                $single_user->setEmail($email);
            }
            if(!$password){
                $single_user->setPlainPassword($password);
            }
            $single_user->setEnabled(1);
            $em = $this->getDoctrine()->getManager();
            $em->persist($single_user);
            $em->flush();
            //update success
            $this->addFlash(
                'notice',
                'user updated'
            );
            return $this->redirectToRoute('crud_list');
        }
        return $this->render('crud/edit.html.twig',array(
            'form' => $form->createView(),
            'user' => $single_user
        ));
    }

    /**
     * @Route("/crud/detail/{id}", name="crud_detail")
     */
    public function detailAction($id)
    {
        //get single user detail
        $single_user = $this->getDoctrine()
            ->getRepository('AppBundle:user')
            ->find($id);
        return $this->render('crud/detail.html.twig',array(
            'user' => $single_user  
        ));    
    }

    /**
     * @Route("/crud/delete/{id}", name="crud_delete")
     */
    public function deleteAction($id)
    {
        // delete user
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:user')->find($id);
        $em->remove($user);
        $em->flush();
        $this->addFlash(
            'notice',
            'user removed'
        );
        return $this->redirectToRoute('crud_list');
    }

}
