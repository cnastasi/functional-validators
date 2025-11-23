# Contributing to Value Objects

Thank you for your interest in contributing to this project! This document provides guidelines and instructions for contributing.

## Code of Conduct

- Be respectful and inclusive
- Welcome newcomers and help them learn
- Focus on constructive feedback

## How to Contribute

### Reporting Bugs

If you find a bug, please open an issue with:
- A clear title and description
- Steps to reproduce the issue
- Expected vs actual behavior
- PHP version and environment details
- Code examples if possible

### Suggesting Features

Feature suggestions are welcome! Please open an issue with:
- A clear description of the feature
- Use cases and examples
- Why this feature would be useful

### Pull Requests

1. **Fork the repository**
2. **Create a feature branch** (`git checkout -b feature/your-feature-name`)
3. **Make your changes**
   - Follow the existing code style
   - Add tests for new features
   - Update documentation as needed
4. **Run tests** (`composer test`)
5. **Commit your changes** (`git commit -m 'Add feature: description'`)
6. **Push to your branch** (`git push origin feature/your-feature-name`)
7. **Open a Pull Request**

## Development Setup

```bash
# Clone your fork
git clone https://github.com/your-username/value-objects.git
cd value-objects

# Install dependencies
composer install

# Run tests
composer test
```

## Code Style

- Follow PSR-12 coding standards
- Use type hints wherever possible
- Add docblocks for public methods
- Keep functions small and focused

## Testing

- All new features must include tests
- Tests should be in the `tests/` directory
- Use Pest PHP for testing
- Aim for good test coverage

## Documentation

- Update README.md if adding new features
- Add code examples for new validators or patterns
- Keep inline comments clear and concise

## Questions?

Feel free to open an issue for any questions or discussions!

