<?php

namespace App\Form\Model;

use App\Validator\UniqueUser;
use Symfony\Component\Validator\Constraints as Assert;

class UserRegistrationFormModel
{
    /**
     * @Assert\NotBlank(message="Please enter an email")
     * @Assert\Email()
     * @UniqueUser()
     */
    public $email;

    /**
     * @Assert\NotBlank(message="Choose a password!")
     * @Assert\Length(min=5, minMessage="Come on, you can think of a password longer than that!")
     */
    public $plainPassword;

    /**
     * @Assert\NotBlank(message="Please enter your name!")
     */
    public $firstName;

    /**
     * @Assert\IsTrue(message="I know, it's silly, but you must agree to our terms.")
     */
    public $agreeTerms;

    public $subscribeToNewsletter;

    /**
     * @Assert\NotBlank(message="Set a phone number")
     * @Assert\Length(max=50, maxMessage="Type in a shorter phone number")
     */
    public $telephone;

    /**
     * @Assert\NotBlank()
     */
    public $userType;
}
