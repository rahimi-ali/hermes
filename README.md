# Hermes

Swift messenger PHP framework with standardized interfaces, extreme customizability,
seamless async support, and high performance.

WARNING: This is still in very early stages, some core functionality is missing and
tests are a work in progress as well so for now it is just a "build in public" concept. :)

## Design goals

1. __Asynchronous Aware:__
Tailored for use with asynchronous tools such as Swoole, OpenSwoole, and RoadRunner,
enabling efficient handling of concurrent requests.

2. __Standard Surface:__
Adheres to standard interfaces(e.g. PSR) as much as possible to make it interoperable
with existing tools.

3. __Customizable Core:__
There are clear interfaces that can be implemented however you like and replace
existing first-party components.

4. __Minimized Magic:__
Magic(magic method, reflection, alike) makes following the code in your editor harder,
reduce type safety and generally degrade performance therefore their use is avoided
unless absolutely necessary for a reasonable DevEx(e.g. reflection for DI auto wiring).

5. __Performance Priority:__
Emphasizes performance by using abstractions judiciously, ensuring minimal overhead.
Leverages libraries and tools that are optimized for speed and efficiency.

## Documentation

```php
// TODO: implement this!
```

But you can get an idea of how things work by looking at the `example` directory.

## Feature List

- [x] Dependency Injection
- [X] HTTP Server
- [ ] CLI 
- [ ] Queue System
- [ ] WebSocket Server
