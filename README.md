# RTE Processing Tester TYPO3 Extension

TYPO3 extension for testing rich text editor content processing configuration.

You can use this extension (backend module) to quickly test your RTE processing configuration. This is a developer tool.

This is not a tool to configure the RTE itself!

## Status

This tool might need some adjustments for TYPO3 8.x and later. It was once build when the HtmlArea RTE was used in TYPO3.
Nowadays the CKEditor is used. The processing is still the same but the configuration slightly changed.

### To do

* provide a meaningful configuration as starting point
* support the YAML configuration introduced with CKEditor
* support custom path to config file
    
## Usage

Further information: https://docs.typo3.org/p/colorcube/rte-proc-tester/master/en-us/

### Dependencies

* TYPO3 6.2 - 8.7

### Installation

#### Installation using Composer

In your Composer based TYPO3 project root, just do `composer require colorcube/rte-proc-tester`. 

#### Installation as extension from TYPO3 Extension Repository (TER)

Download and install the extension with the extension manager module.

## Contribute

- Send pull requests to the repository. <https://github.com/colorcube/rte_proc_tester>
- Use the issue tracker for feedback and discussions. <https://github.com/colorcube/rte_proc_tester/issues>