# Data Records

## Project

## Section

## Contract Item

## Component

## Component Item

## Material Quantity

## Material Quantity Request

## Purchase Order

## Material Canvass

## Direct Material Quantity Request

## Direct Purchase Order

## Direct Material Canvass


2.0mm thk x 2" x 3" x 6.0m [ Length ]
# Direct Data Record Structure

## Project Material Budget Record Hierarchy
Description: The budget record follows a strict hierarchy that has a one parent multiple child relationship. Currently the implementation of these structure is as follows:


```text
Project
    └─ Sections
        └─ Contract Items
            └─ Components
                └─ Component Items
                    └─ Material Quantities
```



## Procurement Records 
Description: Before a Purchase Order Record

```text
Material Quantity Request
    └─ Material Quantity Request Items
```





----------


