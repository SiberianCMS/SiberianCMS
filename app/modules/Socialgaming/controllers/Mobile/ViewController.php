<?php

class Socialgaming_Mobile_ViewController extends Application_Controller_Mobile_Default
{

    public function findallAction() {

        if($this->getRequest()->getParam('value_id')) {
            // Sociel Gaming
            $application = $this->getApplication();
            $option = $this->getCurrentOptionValue();

            $current_game = new Socialgaming_Model_Game();
            $current_game->findCurrent($application->getId());

            if(!$current_game->getId()) {
                $current_game->findDefault();
            }

            list($start, $end) = $current_game->getFromDateToDate();

            $log = new LoyaltyCard_Model_Customer_Log();
            $customers = $log->getBestCustomers($start->toString('y-MM-dd HH:mm:ss'), $end->toString('y-MM-dd HH:mm:ss'), false);
            $team_leader = $customers->current();
            $customers->removeCurrent();

            $data = array(
                "icon_url" => $this->_getColorizedImage($option->getIconId(), $application->getBlock('background')->getColor()),
                "game" => array(
                    "period" => strtoupper($current_game->getGamePeriodLabel())
                ),
                "team_leader" => array(),
                "customers" => array()
            );

            if($team_leader) {
                $data["team_leader"] = array(
                    "id" => $team_leader->getId(),
                    "image_url" => $team_leader->getImageLink(),
                    "name" => $team_leader->getFirstname(). ' ' . mb_substr($team_leader->getLastname(), 0, 1, "UTF-8") . '.',
                    "number_of_points" => $this->_('%s point%s', $team_leader->getNumberOfPoints(), $team_leader->getNumberOfPoints() > 1 ? 's' : ''),
                );
            }

            if($customers->count()) {
                foreach($customers as $customer) {

                    $data["customers"][] = array(
                        "id" => $customer->getId(),
                        "image_url" => $customer->getImageLink(),
                        "name" => $customer->getFirstname(). ' ' . mb_substr($customer->getLastname(), 0, 1, "UTF-8") . '.',
                        "number_of_points" => $this->_('%s point%s', $customer->getNumberOfPoints(),  $customer->getNumberOfPoints() > 1 ? 's' : ''),
                    );

                }
            }

            $data['page_title'] = $option->getTabbarName();

            $this->_sendHtml($data);

        }
    }

}