try:
    from setuptools import setup
except ImportError:
    from distutils.core import setup

setup(
    name='ec2instancespricing',
    packages=[
        'ec2instancespricing',
  ],
  version='0.1',
  scripts=['ec2instancespricing/ec2instancespricing.py'],
  description='Tool to get ec2 instance prices',
  author='Eran Sandler',
  author_email='eran@sandler.co.il',
  license='Other/Proprietary',
  keywords=['ec2', 'pricing', 'cloud'],  # arbitrary keywords,
  url='https://github.com/erans/ec2instancespricing',
  classifiers=[
        "Development Status :: 5 - Production/Stable",
        "Environment :: Console",
        "Intended Audience :: Developers",
        "Intended Audience :: System Administrators",
        "Operating System :: OS Independent",
        "Programming Language :: Python :: 2.7",
        "Programming Language :: Python :: 3",
        "Topic :: System :: Systems Administration",
        "Topic :: Utilities"
  ],
  install_requires=[],
)
