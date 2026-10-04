<?php

namespace App\Controller;
use Cake\Mailer\Mailer;
use Cake\Http\Exception\BadRequestException;

/**
 * Contact Controller
 *
 */
class ContactController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function submit()
    {
        $this->autoRender = false; // Disable view rendering
        $this->request->allowMethod(['post']); // Allow only POST request
        
        $data = $this->request->getData();

        $name = trim((string)($data['name'] ?? ''));
        $email = trim((string)($data['email'] ?? ''));
        $subject = trim((string)($data['subject'] ?? ''));
        $comments = trim((string)($data['comments'] ?? ''));
        $number = trim((string)($data['number'] ?? ''));

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $subject === '' || $comments === '') {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['status' => 'error', 'message' => 'Please provide your name, a valid email, a subject and a message.']));
        }

        // Send Email (requires a working mail transport — failures are
        // reported honestly, never swallowed as success).
        try {
            $mailer = new Mailer('default');
            $mailer->setFrom(['noreply@fastnetstays.com' => 'FastNet Stays'])
                ->setTo('support@fastnetstays.com')
                ->setReplyTo($email)
                ->setSubject('[Contact] ' . mb_substr($subject, 0, 120))
                ->deliver("Name: {$name}\nEmail: {$email}\nNumber: {$number}\n\n{$comments}");

            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['status' => 'success', 'message' => 'Your message has been sent successfully!']));
        } catch (\Exception $e) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['status' => 'error', 'message' => 'Failed to send email.']));
        }
    }
}