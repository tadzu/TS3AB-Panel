<?php
 /**
  * Information
  * @Author: xares
  * @Date:   24-05-2020 18:51
  * @Filename: Prev.php
  * @Project: xDashTS3AudioBot
  *
  * Contact
  * @Email: xares.scripts@gmail.com
  * @TeamSpeak: x-scripts.pl, jutuby.net
  *
  * Modify
  * @Last Modified by:   xares
  * @Last Modified time: 24-05-2020 18:52
  *
  * @Copyright(C) 2020 x-Scripts
  */

  class Prev extends CI_Controller {
    public function index() {
      $this->output->set_content_type('application/json')->set_status_header(200);
      if(!$this->session->userdata('logged')) {
        return $this->output->set_output(printJson(false,'Najpierw się zaloguj!'));
      }

      $permissions = permission(['viewAllBots','manageMusic']);
      if(!$permissions['manageMusic']) {
        return $this->output->set_output(printJson(false,'Nie posiadasz dostępu!'));
      }

      $req = request($this->input->post(),['botID','value'],['Podaj id bota!','Podaj czy ma losować utwory!']);
      if(!$req['success']) {
        return $this->output->set_output(printJson(false,$req['response']));
      }
      $req = $req['response'];

      if($permissions['viewAllBots']) {
        if(!$this->db->query("SELECT * FROM `xDashBotList` WHERE `id` = '{$req['botID']}'")->num_rows()) {
          return $this->output->set_output(printJson(false,'Nie znaleziono bota!'));
        }
      } else {
        if(!$this->db->query("SELECT * FROM `xDashBotsUsers` WHERE `username` = '{$this->session->userdata('login')}' AND `botID` = '{$req['botID']}'")->num_rows()) {
          return $this->output->set_output(printJson(false,'Nie znaleziono bota!'));
        }
      }

      $list = json_decode($this->ts3ab->botList(),true);
      if(isset($list['ErrorCode'])) {
        return $this->output->set_output(printJson(false,$list['ErrorMessage']));
      }

      foreach($list as $index) {
        if($index['Name'] == $req['botID']) {
          if($index['Status'] == 2) {
            $this->ts3ab->setBotID($index['Id']);
            $player = json_decode($this->ts3ab->prev(),true);
            return $this->output->set_output(printJson(true,'Przełączono na poprzedni utwór'));
          }
        }
      }

      return $this->output->set_output(printJson(false,'Bot jest wyłączony'));
    }
  }
 ?>
