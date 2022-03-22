## Upgrade notes from SSFW team
- this file contains information about various breaking changes introduced by the SSFW team
- it is possible that some of the changes that are easy to implement, or are of a smaller nature may be skipped from these notes
- each change note contains information about the original US, MR, reason behind the changes, most significant changes, and tips on how to implement them

### Intorduction of the rules of hooks ES lint rule
- [FWCC-831](https://shopsys.atlassian.net/browse/FWCC-831)
- [FWCC-831 implemented rules of hooks](https://gitlab.shopsys.cz/ss6-projects/ssfwcc/-/merge_requests/515) 
- the reasons these changes were introduced:
  - hooks are a complex topic to gather and it may be problematic for new developers working on JS SF
  - violation of the rules of hooks were becoming more and more often which could result in run-time errors
  - having a static check is going to improve the standards of the code
- most significant changes 
  - all connector methods were renamed from "getSomething" to "useSomething" (e.g. getOrderDetail to useOrderDetail)
  - usePagination hooks now uses useMemo hook internally
  - additional test package was added to allow for testing of hooks
  - useGetInternationalizedStaticUrls was renamed to getInternationalizedStaticUrls as it is not a hook and was moved from hooks folder to utils folder
- tips on how to implement these changes
  - make sure that every custom hook's name starts with a "use" prefix
    - custom hooks are all methods that call other hooks internally, and to which the rules of hooks apply
  - make sure that no hook is called conditionally (after or inside an if/else block, after early return)
