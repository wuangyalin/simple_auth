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
     * @Route("/crud/edit/{id}", name="crud_edit")
     */
    public function editAction($id,Request $request)
    {
        $single_user = $this->getDoctrine()
        ->getRepository('AppBundle:user')
        ->find($id);

        $form = $this->createFormBuilder($single_user)
            ->add('username', TextType::class, array(
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
            ->add('user_role', ChoiceType::class, array(
                'attr' => array('class' => 'form-control', 
                'style' => 'margin-bottom:15px'),
                'choices' => array(
                    'Admin User' => 'ROLE_ADMIN',
                    'General User' => 'ROLE_USER'
                    )
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
            $userrole = $form['user_role']->getData();
            if(!$email){
                $single_user->setEmail($email);
            }
            $single_user->setUserRole($userrole);
            $single_user->addRole($userrole);
            $em = $this->getDoctrine()->getManager();
            $em->persist($single_user);
            $em->flush();
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
