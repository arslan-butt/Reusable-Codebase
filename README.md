# Reusable PHP Services

This repository contains a collection of **generic and reusable PHP classes/services** that can be easily integrated into any project. These classes are designed to handle common tasks, provide foundational logic, and promote clean, maintainable code.

---

## Features

- **Reusable Logic**: Classes and services that can be used across multiple projects.
- **Modular Design**: Each class is self-contained and follows best practices for scalability and maintainability.
- **Generic and Framework-Agnostic**: Designed to work in any PHP project, regardless of the framework.
- **Easy to Extend**: Classes are built with extensibility in mind, allowing you to customize them for your specific needs.

---

## Included Classes/Services

### **1. BaseService**
An abstract class that provides a foundation for building consistent and reusable services. It includes:
- Built-in validation for input data.
- Utility methods for handling common data operations.

### **2. LegacyApiService**
A flexible service for interacting with legacy APIs. Features include:
- Support for multiple HTTP methods (`GET`, `POST`, `PUT`, `DELETE`).
- Retry logic for failed requests.
- File upload support.

### **3. Utility Methods**
Helper methods for:
- Handling nullable values (`nullOrValue`).
- Parsing dates (`nullOrDate`).
- Default fallback values (`valueOrFalse`, `valueOrTrue`).

---

## Usage

These classes can be used in any PHP project. Simply include the classes in your project and extend or use them as needed.

### Example: Using `BaseService`
```php
class UserService extends BaseService
{
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'email' => 'required|email',
        ];
    }

    public function createUser(array $data): void
    {
        $this->validate($data);

        $name = $this->nullOrValue($data, 'name');
        // Add your logic here...
    }
}
```
