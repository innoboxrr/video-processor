import subprocess
import os

def get_latest_tag():
    # Cambia al directorio raíz del repositorio
    repo_root = os.path.abspath(os.path.join(os.path.dirname(__file__), '..'))
    os.chdir(repo_root)
    
    # Imprime el directorio de trabajo actual para la depuración
    print(f"Current working directory: {os.getcwd()}")
    
    result = subprocess.run(['git', 'describe', '--tags', '--abbrev=0'], stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    if result.returncode != 0:
        print(f"Error: {result.stderr.decode('utf-8')}")
        raise Exception("Failed to get the latest tag")
    tag = result.stdout.decode('utf-8').strip()
    print(f"Latest tag: {tag}")
    return tag

def increment_version(version):
    major, minor, patch = map(int, version.split('.'))
    patch += 1
    return f"{major}.{minor}.{patch}"

def main():
    latest_tag = get_latest_tag()
    new_tag = increment_version(latest_tag)
    
    subprocess.run(['git', 'tag', new_tag])
    subprocess.run(['git', 'push', 'origin', new_tag])

    print(f"Tag incremented from {latest_tag} to {new_tag}")

if __name__ == "__main__":
    main()
import subprocess
import os

def get_latest_tag():
    # Cambia al directorio raíz del repositorio
    repo_root = os.path.abspath(os.path.join(os.path.dirname(__file__), '..'))
    os.chdir(repo_root)
    
    # Imprime el directorio de trabajo actual para la depuración
    print(f"Current working directory: {os.getcwd()}")
    
    result = subprocess.run(['git', 'describe', '--tags', '--abbrev=0'], stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    if result.returncode != 0:
        print(f"Error: {result.stderr.decode('utf-8')}")
        raise Exception("Failed to get the latest tag")
    tag = result.stdout.decode('utf-8').strip()
    print(f"Latest tag: {tag}")
    return tag

def increment_version(version):
    major, minor, patch = map(int, version.split('.'))
    patch += 1
    return f"{major}.{minor}.{patch}"

def main():
    latest_tag = get_latest_tag()
    new_tag = increment_version(latest_tag)
    
    subprocess.run(['git', 'tag', new_tag])
    subprocess.run(['git', 'push', 'origin', new_tag])

    print(f"Tag incremented from {latest_tag} to {new_tag}")

if __name__ == "__main__":
    main()
