<?php
/**
 * Created by PhpStorm.
 * User: mariusz
 * Date: 09.03.16
 * Time: 22:46
 */

namespace AuthModuleBundle\Controller;

use AuthModuleBundle\Entity\User;
use Doctrine\DBAL\Types\DateTimeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Tests\Extension\Core\Type\PasswordTypeTest;
use Symfony\Component\Form\Tests\Extension\Core\Type\RepeatedTypeTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Session\Session;

class AuthController extends Controller
{
    /**
     * @Route("/auth", name="auth_main")
     */
    public function mainAction(Request $request)
    {

        $messages = $this->initMessages($request);

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            //throw $this->createAccessDeniedException();
        }

        // the above is a shortcut for this
        $user = $this->get('security.token_storage')->getToken()->getUser();


        // replace this example code with whatever you need
        return $this->render('auth/main.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
            'user' => $user,
            'messages' => $messages,
        ]);
    }

    /**
     * @Route("/auth/confirm", name="auth_confirm")
     */
    public function confirmAction(Request $request)
    {
        $token = $request->query->get('token');//$_GET['token'];

        $session = $request->getSession();

        // wczytaj
        $user = $this->getDoctrine()
            ->getRepository('AuthModuleBundle:User')
            ->findOneByToken($token);

        if ($user) {

            $em = $this->getDoctrine()->getManager();

            // zapisz
            $user->setIsConfirmed(true);
            $em->persist($user);
            $em->flush();

            $session->getFlashBag()->add('success', 'Użytkowniku, Twój adres e-mail został potwierdzony');
        }
        else {
            $session->getFlashBag()->add('warning', 'Link już wygasł');
        }

        return $this->redirectToRoute('auth_main');

    }

    /**
     * @Route("/auth/register", name="auth_register")
     */
    public function registerAction(Request $request)
    {
        $messages = $this->initMessages($request);

        $user = new User();
        $form = $this->createFormBuilder($user)
            ->add('email', EmailType::class, array('required' => true,'label' => 'Adres e-mail','attr' => array('class' => 'form-control', 'style'=> 'margin-bottom:10px;')))
            ->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'Hasła muszą się zgadzać.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => true,
                'first_options'  => array('label' => 'Hasło','attr' => array('class' => 'form-control', 'style'=> 'margin-bottom:10px;')),
                'second_options' => array('label' => 'Powtórz hasło','attr' => array('class' => 'form-control', 'style'=> 'margin-bottom:10px;')),
            ))
            ->add('firstname', TextType::class, array('required' => false,'label' => 'Imię','attr' => array('class' => 'form-control', 'style'=> 'margin-bottom:10px;')))
            ->add('lastname', TextType::class, array('required' => false,'label' => 'Nazwisko','attr' => array('class' => 'form-control', 'style'=> 'margin-bottom:10px;')))
            ->add('birthday', BirthdayType::class, array('required' => true,'label' => 'Data urodzin','attr' => array('class' => 'form-control', 'style'=> 'margin-bottom:10px;')))
            ->add('save', SubmitType::class, array('label' => 'Wyślij','attr' => array('class' => 'btn btn-lg btn-primary btn-block', 'style'=> 'margin-bottom:10px;')))
            ->getForm();

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {

            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $user->setIsConfirmed(0);
            $user->setRegistered(new \DateTime("now"));

            $token = $user->getConfirmationToken();

            $url='?token='.$token;
            $user->setToken($token);

            // save the User!
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $message = \Swift_Message::newInstance()
                ->setSubject('Rejestracja')
                ->setFrom('send@example.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'auth/email.html.twig',
                        array('name' => $user->getFirstname().' '.$user->getLastname(),
                        'confirmationUrl' => $url)
                    ),
                    'text/html'
                );
            $this->get('mailer')->send($message);

            //  set flash message to the user
            $session = $request->getSession();
            $session->getFlashBag()->add('success', 'Twoje konto zostało zarejestrowane.');

            return $this->redirectToRoute('auth_main');
        }

        // replace this example code with whatever you need
        return $this->render('auth/register.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
            'form' => $form->createView(),
            'messages' => $messages,
        ]);
    }

    /**
     * @Route("/auth/login", name="auth_login")
     */
    public function loginAction(Request $request)
    {

        $messages = $this->initMessages($request);

        $user = new User();
        $form = $this->createFormBuilder($user)
            ->add('email', EmailType::class, array('required' => true,'label' => 'Adres e-mail','attr' => array('class' => 'form-control', 'style'=> 'margin-bottom:10px;')))
            ->add('password', PasswordType::class, array('required' => true,'attr' => array('class' => 'form-control', 'style'=> 'margin-bottom:10px;')))
            ->add('save', SubmitType::class, array('label' => 'Wyślij','attr' => array('class' => 'btn btn-lg btn-primary btn-block', 'style'=> 'margin-bottom:10px;')))
            ->getForm();

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {

            $authenticationUtils = $this->get('security.authentication_utils');

            // get the login error if there is one
            $error = $authenticationUtils->getLastAuthenticationError();

            // last username entered by the user
            $lastUsername = $authenticationUtils->getLastUsername();

//            return $this->render('auth/login.html.twig', [
//                'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
//                'form' => $form->createView(),
//                'last_username' => $lastUsername,
//                'error'         => $error,
//                'messages' => $messages,
//            ]);

            $session = $request->getSession();
            $session->getFlashBag()->add('success', 'Użytkownik zalogowany');

            return $this->redirectToRoute('auth_main');
        }

        return $this->render('auth/login.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
            'form' => $form->createView(),
            'error' => '',
            'messages' => $messages,
        ]);
    }

    private function initMessages($request) {

        $session = $request->getSession();

        $messages = '';

        // display warnings
        foreach ($session->getFlashBag()->get('warning', array()) as $message) {
            $messages.= '<div class="alert alert-warning">'.$message.'</div>';
        }
        // display errors
        foreach ($session->getFlashBag()->get('error', array()) as $message) {
            $messages.= '<div class="alert alert-danger">'.$message.'</div>';
        }
        // display success
        foreach ($session->getFlashBag()->get('success', array()) as $message) {
            $messages.= '<div class="alert alert-success">'.$message.'</div>';
        }

        return $messages;
    }
}
