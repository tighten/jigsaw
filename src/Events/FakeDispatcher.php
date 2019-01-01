<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Events;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;

 class FakeDispatcher implements DispatcherContract
 {
     public function listen($events, $listener): void
     {
     }

     public function hasListeners(string $eventName): bool
     {
     }

     public function subscribe($subscriber): void
     {
     }

     public function until($event, $payload = []): ?array
     {
         return null;
     }

     public function dispatch($event, $payload = [], bool $halt = false): ?array
     {
     }

     public function push($event, $payload = []): void
     {
     }

     public function flush($event): void
     {
     }

     public function forget($event): void
     {
     }

     public function forgetPushed(): void
     {
     }
 }
