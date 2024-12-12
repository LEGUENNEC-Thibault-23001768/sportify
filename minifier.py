import os
import sys
import jsmin
import cssmin

def minify_directory(input_dir, output_dir, file_type):
    """Minifies all files of a given type in the input directory and saves them to the output directory."""

    for root, _, files in os.walk(input_dir):
        for file in files:
            if file.endswith(file_type):
                input_path = os.path.join(root, file)
                relative_path = os.path.relpath(input_path, input_dir)
                output_path = os.path.join(output_dir, relative_path)

                os.makedirs(os.path.dirname(output_path), exist_ok=True)

                try:
                    with open(input_path, 'r') as f_in:
                        if file_type == '.js':
                            minified_content = jsmin.jsmin(f_in.read())
                        elif file_type == '.css':
                            minified_content = cssmin.cssmin(f_in.read())
                        else:
                            raise ValueError("Unsupported file type")

                    with open(output_path, 'w') as f_out:
                        f_out.write(minified_content)

                    print(f"Minified: {relative_path} -> {os.path.relpath(output_path, '.')}")

                except Exception as e:
                    print(f"Error minifying {relative_path}: {e}", file=sys.stderr)

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: python minify.py <source_directory> <destination_directory>")
        sys.exit(1)

    src_base_dir = sys.argv[1]
    dest_base_dir = sys.argv[2]

    src_css_dir = os.path.join(src_base_dir, 'Views', '_assets', 'css')
    src_js_dir = os.path.join(src_base_dir, 'Views', '_assets', 'js')

    dest_css_dir = os.path.join(dest_base_dir, '_assets', 'css')
    dest_js_dir = os.path.join(dest_base_dir, '_assets', 'js')

    if os.path.exists(src_css_dir):
        minify_directory(src_css_dir, dest_css_dir, '.css')
    else:
        print(f"Warning: Source CSS directory not found: {src_css_dir}", file=sys.stderr)

    if os.path.exists(src_js_dir):
        minify_directory(src_js_dir, dest_js_dir, '.js')
    else:
        print(f"Warning: Source JS directory not found: {src_js_dir}", file=sys.stderr)