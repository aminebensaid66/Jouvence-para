# Tunisia geography — JP-GEO-001

The domain supports immutable geography nodes at three levels: governorate → delegation → locality. IDs are machine values and never change when French/Arabic labels change.

Launch seeds the 24 governorates because the approved nationwide flat-rate carrier rules operate at national/governorate depth and do not provide an approved delegation/locality routing dataset. Deeper nodes can be supplied later through the `jouvence_para_geography_nodes` extension after an approved data source exists. The model already validates parent/child paths and includes a nullable Arabic-label field without shipping Arabic storefront content.
