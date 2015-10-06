try:
    from setuptools import setup
except ImportError:
    from distutils.core import setup

# As a double precaution, lock this down to uploads to internal
#from distutils import config
#config.PyPIRCCommand.DEFAULT_REPOSITORY = 'internal'

setup(
  name='ec2instancespricing',
  packages=[
        'ec2instancespricing',
  ],
  version='0.1',
  scripts=['ec2instancespricing/ec2instancespricing.py'],
  description='Tool to get ec2 instance prices',
  author='Eran Sandler',
  author_email='@erans',
  license='Other/Proprietary',
  keywords=['ec2', 'pricing'],  # arbitrary keywords,
  url='https://github.com/erans/ec2instancespricing',
  classifiers=[],
  install_requires=[],
)
