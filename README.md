# Web Application

This repository contains a web application built using modern web technologies. The project demonstrates proficiency in front-end and back-end development, integrating features to provide a seamless user experience.

## Features

- **Responsive Design**: Optimized for various devices, including desktops, tablets, and mobile phones.
- **Interactive UI**: Includes animations and dynamic interactions to enhance usability.
- **Authentication**: Secure user login and registration functionality.
- **API Integration**: Utilizes RESTful APIs to manage data efficiently.
- **Task Management**: Features a to-do list app with CRUD (Create, Read, Update, Delete) functionality.
- **Database Integration**: Powered by a relational database for persistent data storage.

## Technologies Used

- **Front-End**:  
  - HTML, CSS, JavaScript  
  - Vue.js for reactive components and UI logic  
- **Back-End**:  
  - Laravel (version 11) for building robust and scalable APIs  
- **Authentication**:  
  - Laravel Sanctum for secure API authentication  
- **Database**:  
  - PostgreSQL  
- **Development Tools**:  
  - npm for package management  
  - Interactive shell in Kali Linux for testing and debugging  

## Installation

Follow these steps to set up the project locally:

1. Clone this repository:
   ```bash
   git clone https://github.com/Laawrr/Web-Application.git
   ```
2. Navigate to the project directory:
   ```bash
   cd Web-Application
   cd web-app
   ```
3. Install dependencies:
   ```bash
   npm install
   composer install
   ```
4. Configure the `.env` file:
   - Copy `.env.example` to `.env`.
   - Update the database credentials and other environment variables.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. Run database migrations:
   ```bash
   php artisan migrate
   ```

6. Start the development server:
   ```bash
   npm run dev
   php artisan serve
   ```

7. Access the application at:
   ```
   http://localhost:8000
   ```

## Usage

1. Register or log in to access the application.
2. Create, edit, and manage your tasks using the to-do list feature.
3. Interact with the user-friendly interface for seamless navigation.

## Contributing

Contributions are welcome! To contribute:  
1. Fork the repository.  
2. Create a feature branch:  
   ```bash
   git checkout -b feature-name
   ```  
3. Commit your changes:  
   ```bash
   git commit -m "Add feature name"
   ```  
4. Push to the branch:  
   ```bash
   git push origin feature-name
   ```  
5. Submit a pull request.

## License

This project is licensed under the [MIT License](LICENSE).
