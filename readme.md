# Parallelizer

Parallelizer to easily parallelize symfony processes

## Install

```
composer install
```

## Usage

```
use Parallelizer\Parallelizer;

// we want to run 2 processes in parallel
$parallelizer = new Parallelizer(2);

$parallelizer->add($processA, 'processA');
$parallelizer->add($processB, 'processB');
// ....

var_dump($parallelizer->run());

```

## Test

```
phpunit
```

## Credits

Project structure inspired by
[Negotiation](https://github.com/willdurand/Negotiation) by
[willdurand](https://github.com/willdurand).

## License

php-dmtx is released under the MIT License. See the bundled LICENSE file for
details.
