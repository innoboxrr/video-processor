import subprocess

def get_latest_tag():
    result = subprocess.run(['git', 'describe', '--tags', '--abbrev=0'], stdout=subprocess.PIPE, stderr=subprocess.DEVNULL)
    tag = result.stdout.decode('utf-8').strip()
    print(f"Latest tag: {tag}")
    #if not tag:
        #return '0.0.0'  # return an initial version if no tags are found
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
