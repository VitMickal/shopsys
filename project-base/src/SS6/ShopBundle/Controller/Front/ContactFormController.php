<?php

namespace SS6\ShopBundle\Controller\Front;

use SS6\ShopBundle\Component\Translation\Translator;
use SS6\ShopBundle\Controller\Front\BaseController;
use SS6\ShopBundle\Form\Front\Contact\ContactFormType;
use SS6\ShopBundle\Model\ContactForm\ContactFormData;
use SS6\ShopBundle\Model\ContactForm\ContactFormFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ContactFormController extends BaseController {

	/**
	 * @var \SS6\ShopBundle\Model\ContactForm\ContactFormFacade
	 */
	private $contactFormFacade;

	/**
	 * @var \SS6\ShopBundle\Component\Translation\Translator
	 */
	private $translator;

	public function __construct(ContactFormFacade $contactFormFacade, Translator $translator) {
		$this->contactFormFacade = $contactFormFacade;
		$this->translator = $translator;
	}

	/**
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function sendAction(Request $request) {
		$form = $this->createForm(
			new ContactFormType(),
			new ContactFormData(),
			[
				'action' => $this->generateUrl('front_contact_form_send'),
				'method' => 'POST',
			]
		);
		$form->handleRequest($request);

		$message = '';
		if ($form->isValid()) {
			$contactFormData = $form->getData();

			try {
				$this->contactFormFacade->sendMail($contactFormData);
				$form = $this->createForm(
					new ContactFormType(),
					new ContactFormData(),
					[
						'action' => $this->generateUrl('front_contact_form_send'),
						'method' => 'POST',
					]
				);
				$message = $this->translator->trans('Děkujeme, váš vzkaz byl odeslán.');
			} catch (\SS6\ShopBundle\Model\Mail\Exception\SendMailFailedException $ex) {
				$message = $this->translator->trans('Nastala chyba při odesílání mailu.');
			}

		}

		$contactFormHtml = $this->renderView('@SS6Shop/Front/Content/ContactForm/contactForm.html.twig', [
			'form' => $form->createView(),
		]);

		return new JsonResponse([
			'contactFormHtml' => $contactFormHtml,
			'message' => $message,
		]);

	}

	public function indexAction() {
		$form = $this->createForm(
			new ContactFormType(),
			new ContactFormData(),
			[
				'action' => $this->generateUrl('front_contact_form_send'),
				'method' => 'POST',
			]
		);

		return $this->render('@SS6Shop/Front/Content/ContactForm/contactForm.html.twig', [
			'form' => $form->createView(),
		]);
	}
}