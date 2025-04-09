
Built by https://www.blackbox.ai

---

# Building Materials Marketplace

## Project Overview
The Building Materials Marketplace is a web application designed to connect buyers and sellers of construction materials. Users can sign up as either buyers or sellers, post tenders for materials needed, and submit offers for materials they wish to sell. This platform facilitates the exchange of information and transactions in a user-friendly manner.

## Installation
To set up the Building Materials Marketplace locally, follow these steps:

1. **Clone the repository**:
   ```bash
   git clone https://github.com/your-username/building-materials-marketplace.git
   cd building-materials-marketplace
   ```

2. **Set up a Database**:
   - Make sure you have MySQL or SQLite installed.
   - Create a database and import the necessary tables for `users`, `tenders`, and `offers`.

3. **Configure Database**:
   - Edit the `includes/db_config.php` file to connect to your database with the correct credentials.

4. **Start a local server**:
   - You can use PHP's built-in server:
   ```bash
   php -S localhost:8000
   ```

5. **Access the application**:
   - Open a web browser and go to `http://localhost:8000`.

## Usage
1. **Sign Up**: Create an account by filling out the signup form at `signup.php`.
2. **Login**: Access your account through the login page at `login.php`.
3. **Dashboard**: Depending on your role (buyer or seller), navigate to your respective dashboard:
   - Buyers can post tenders for materials.
   - Sellers can view available tenders and submit offers.
4. **Admin Dashboard**: Admins can manage tenders and offers through `admin_dashboard.php`.

## Features
- User Authentication: Users can register, log in, and manage their profiles.
- Role-Based Access: Different functionalities for buyers, sellers, and admins.
- Tender Posting: Buyers can post tenders for construction materials they need.
- Offer Submission: Sellers can submit offers for posted tenders.
- Dashboard Views: Separate dashboards for buyers, sellers, and admins to manage their tasks effectively.

## Dependencies
The project utilizes the following technologies:
- PHP
- MySQL / SQLite
- Bootstrap (for styling)

Ensure you have the necessary PHP and database extensions enabled for smooth operation.

## Project Structure
Here’s a brief description of the project files and their purposes:

```
.
├── index.php                  # Landing page of the marketplace
├── login.php                  # User login page
├── signup.php                 # User signup page
├── logout.php                 # User logout function
├── buyer_dashboard.php        # Dashboard for buyers to manage tenders
├── seller_dashboard.php       # Dashboard for sellers to view tenders and submit offers
├── admin_dashboard.php        # Dashboard for admin to manage offers and tenders
├── includes/                  # Directory for included PHP files
│   ├── db_config.php          # Database configuration and connection
└── styles/                    # Custom styles (if any)
```

## License
This project is open source and available under the [MIT License](LICENSE).

Feel free to contribute to the project by submitting issues or pull requests. Enjoy building and connecting in the materials marketplace!