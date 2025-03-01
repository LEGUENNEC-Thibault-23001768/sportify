import os

def display_file_content(filepath):
    """Displays the content of a file along with its name."""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        print(f"\n--- Content of {filepath}: ---\n")
        print(content)
        print(f"\n--- End of {filepath} ---\n")
    except FileNotFoundError:
        print(f"Error: File not found: {filepath}")
    except UnicodeDecodeError:
        print(f"Error: Unable to decode file with UTF-8: {filepath}. (It might be a binary file.)")

def find_and_display_files(root_dir, files_to_find):
    """
    Traverses the directory tree starting from root_dir and displays the content of specified files.
    """
    for dirpath, dirnames, filenames in os.walk(root_dir):
        for filename in filenames:
            if filename in files_to_find:
                filepath = os.path.join(dirpath, filename)
                display_file_content(filepath)

if __name__ == "__main__":
    files_to_find = [
        "BookingAPIController.php",
        "Booking.php",
        "booking/index.php",
    ]
    
    root_directory = "."

    find_and_display_files(root_directory, files_to_find)