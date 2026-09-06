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
        
        if (!$data['name'] || !$data['email'] || !$data['number'] || !$data['subject'] || !$data['comments']) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['status' => 'error', 'message' => 'All fields are required']));
        }

        // Send Email (if configured)
        try {
            $mailer = new Mailer('default');
            $mailer->setFrom(['your-email@gmail.com' => 'Geotrip']) # Replace with your email
                ->setTo('receiver-email@gmail.com') # Replace with receiver email
                ->setSubject($data['subject'])
                ->deliver("Name: {$data['name']}\nEmail: {$data['email']}\nSubject: {$data['subject']}\nNumber: {$data['number']}\ncomments: {$data['comments']}");

            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['status' => 'success', 'message' => 'Your message has been sent successfully!']));
        } catch (\Exception $e) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['status' => 'error', 'message' => 'Failed to send email.']));
        }
    }
}