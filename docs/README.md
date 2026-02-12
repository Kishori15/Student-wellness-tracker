
#!/bin/bash

# Prerequisites
echo "Ensure Node.js, npm, and Git are installed on your machine."
echo "If not, download and install them from the official websites:"
echo "Node.js: https://nodejs.org/"
echo "Git: https://git-scm.com/"
read -p "Press [Enter] to continue once prerequisites are installed..."

# Clone the Repository
echo "Cloning the 'Student Wellness Tracker' repository..."
git clone https://github.com/stephendyoungjr/StudentWellnessTracker.git
cd StudentWellnessTracker || { echo "Failed to navigate to project directory"; exit 1; }

# Install Dependencies
echo "Installing dependencies..."
npm install

# Set Up Environment Variables
echo "Setting up environment variables..."
if [ ! -f .env ]; then
    touch .env
    echo "Add your environment variables in the '.env' file."
    echo "Example:"
    echo "REACT_APP_API_KEY=your_api_key" >> .env
    echo "REACT_APP_OTHER_VARIABLE=your_value" >> .env
    echo "Environment variables have been added to .env"
else
    echo ".env file already exists. Please ensure it contains all necessary variables."
fi

# Run the Project
echo "Starting the project..."
npm start

echo "The frontend should now be running on http://localhost:3000."
echo "The backend should be running on the port specified in server.js."

# Access the Application
echo "Open a web browser and go to http://localhost:3000 to view the application."

# Optional Steps
echo "Optional: Run tests with the following command:"
echo "npm test"

echo "Optional: Deploy the project to GitHub Pages with the following command:"
echo "npm run deploy"

echo "Note: Ensure you are using the correct versions of npm and Node.js to avoid compatibility issues."
echo "You can specify required versions in the package.json under 'engines' if needed."

echo "Setup complete. Enjoy using the 'Student Wellness Tracker'!"
