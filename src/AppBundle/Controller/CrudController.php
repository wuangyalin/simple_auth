<?php

namespace AppBundle\Controller;

use AppBundle\Entity\user;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
        return $this->render('crud/index.html.twig',array(
            'users' => $list
        ));
    }

    /**
     * @Route("/crud/create", name="crud_create")
     */
    public function createAction(Request $request,UserPasswordEncoderInterface $encoder)
    {
        $user = new user;
        
        $form = $this->createFormBuilder($user)
            ->add('username', TextType::class,array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('password', PasswordType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('description', TextType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('userrole', ChoiceType::class, array(
                'attr' => array('class' => 'form-control', 
                'style' => 'margin-bottom:15px'),
                'choices' => array(
                    'Admin User' => 'admin',
                    'General User' => 'user'
                )
                ))
            ->add('Save', SubmitType::class, array(
                'attr' => array(
                    'class' => 'btn btn-primary', 
                    'style' => 'margin-bottom:15px'),
                    'label' => 'Create User'))
            ->getForm();

        $form -> handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $username = $form['username']->getData();
            $password = $form['password']->getData();
            //$encoded_password = $encoder->encodePassword($user, $password);
            $description = $form['description']->getData();
            $userrole = $form['userrole']->getData();
            $createDate = new\DateTime('now');

            $user->setUsername($username);
            $user->setPassword($password);
            $user->setDescription($description);
            $user->setCreatedate($createDate);
            $user->setUserrole($userrole);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash(
                'notice',
                'user added'
            );
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
        // replace this example code with whatever you need
        return $this->render('crud/edit.html.twig');
    }

    /**
     * @Route("/crud/detail/{id}", name="crud_detail")
     */
    public function detailAction($id)
    {
        // replace this example code with whatever you need
        return $this->render('crud/detail.html.twig');
    }

    /**
     * @Route("/crud/delete/{id}", name="crud_delete")
     */
    public function deleteAction($id)
    {
        die('remove this user');
    }

}
