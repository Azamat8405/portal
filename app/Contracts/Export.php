<?php

namespace App\Contracts;

interface Export {

  /**
   * Push a new event to all clients.
   *
   * @param  string  $event
   * @param  array  $data
   * @return void
   */
  public function push($event, array $data);

}