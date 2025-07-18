---
description: "Session Continuity Framework - Multi-Agent Investigation Handoffs"
created: "2025-07-18"
status: "Active Framework"
---

# Session Continuity Framework: Multi-Agent Investigation Handoffs

## 🎯 Purpose
This document enables seamless handoffs between AI agents when investigating complex issues across multiple sessions. When context limits are reached, copy this framework to a new agent to continue the investigation without losing progress.

## 📋 Quick Start: New Agent Onboarding

### For New Agent (Copy This Section)
```
CONTEXT: I'm continuing a GraphQL investigation started by a previous agent. 

CURRENT ISSUE: PromotedCategoriesQuery and SliderItemsQuery return empty results on first load but work after reload on GitHub preview branches.

CURRENT STATUS: [Check session-investigation-findings.md for latest status]

NEXT STEPS: [Check root-cause-debugging-plan.md for current iteration]

KEY FILES TO READ:
- .claude/session-investigation-findings.md (investigation history)
- .claude/root-cause-debugging-plan.md (current debugging plan)
- .claude/session-continuity-framework.md (this file)
```

## 📁 Master Documentation Structure

### Core Investigation Files
1. **`.claude/session-investigation-findings.md`** - **MASTER STATUS**
   - Original problem statement
   - Investigation history and attempts
   - Current hypothesis and evidence
   - What we've ruled out vs what remains unknown

2. **`.claude/root-cause-debugging-plan.md`** - **CURRENT WORK**
   - Active debugging plan and iteration status
   - Code modifications ready for deployment
   - Local testing validation steps
   - Remote log analysis protocol

3. **`.claude/session-continuity-framework.md`** - **THIS FILE**
   - Framework for multi-agent handoffs
   - Session history tracking
   - Handoff protocols and checklists

### Support Documentation
4. **`.claude/odin-github-cicd-automation.md`** - ODIN server automation
5. **`.claude/tmux-ssh-automation-framework.md`** - tmux automation patterns

## 🔄 Session Handoff Protocol

### When Starting New Session
1. **Read Master Status**: Start with `session-investigation-findings.md`
2. **Check Current Work**: Review `root-cause-debugging-plan.md`
3. **Understand Context**: Read this continuity framework
4. **Update Session Log**: Add your session to the history below
5. **Continue Investigation**: Pick up from current iteration

### When Ending Session
1. **Update Master Status**: Add findings to `session-investigation-findings.md`
2. **Update Current Work**: Modify `root-cause-debugging-plan.md` if needed
3. **Log Session**: Record session in continuity framework
4. **Prepare Handoff**: Update next steps for future agent

## 📊 Session History Log

### Session #1 (2025-07-18)
**Agent**: Claude (Original)
**Duration**: Full session
**Status**: Investigation Setup & Initial Implementation

**Major Accomplishments:**
- ✅ Analyzed cache logic, confirmed it's working correctly
- ✅ Analyzed PHP GraphQL resolvers, identified domain-specific dependencies
- ✅ Implemented comprehensive debugging code in 4 files
- ✅ Created iterative debugging workflow with tmux automation
- ✅ Established local testing validation process

**Files Modified:**
- `project-base/app/src/FrontendApi/Resolver/Category/PromotedCategory/PromotedCategoryRepository.php`
- `packages/framework/src/Model/Slider/SliderItemRepository.php`
- `project-base/app/src/FrontendApi/Resolver/Category/PromotedCategoriesQuery.php`
- `project-base/app/src/FrontendApi/Resolver/SliderItem/SliderItemsQuery.php`

**Key Findings:**
- Cache logic is correct, "cache miss" logs are accurate
- Both failing queries have domain-specific data dependencies
- PromotedCategoriesQuery requires TopCategory records
- SliderItemsQuery requires SliderItem records with date/visibility filters

**Current Hypothesis**: Missing domain-specific data in GitHub preview branches

**Next Steps**: Local testing validation, then deployment and log analysis

**Session End Context**: Ready for local testing validation of debugging code

### Session #2 (Date: TBD)
**Agent**: [Next Agent]
**Duration**: [TBD]
**Status**: [TBD]

**Pick up from**: Local testing validation of debugging code
**Expected work**: 
- Validate logging format on localhost
- Deploy debugging code to PR
- Analyze logs via tmux automation
- Plan next iteration based on findings

## 🔧 Investigation State Tracking

### Current Iteration Status
**Iteration**: #1 - Comprehensive Domain & Query Logging
**Phase**: Implementation Complete, Ready for Local Testing
**Debugging Code**: ✅ Implemented in 4 files
**Local Testing**: ⏳ Pending validation
**Deployment**: ⏳ Awaiting local testing approval
**Log Analysis**: ⏳ Awaiting deployment

### Hypotheses Being Tested
1. **Primary**: Missing domain-specific data (TopCategory, SliderItem records)
2. **Alternative**: Domain configuration issues
3. **Alternative**: Query timing/race conditions
4. **Alternative**: Data filtering problems
5. **Alternative**: Database connection issues

### Evidence Collected
- ✅ Cache logic analysis complete
- ✅ PHP resolver code analysis complete
- ✅ Domain-specific dependencies identified
- ⏳ Local testing validation pending
- ⏳ Production log analysis pending

### Next Major Decisions
1. **If logs show domain data exists**: Focus on query timing or filtering
2. **If logs show domain data missing**: Focus on data seeding issues
3. **If logs show unexpected behavior**: Plan iteration #2 with deeper logging

## 🎯 Critical Context for New Agents

### The Core Problem
- **Environment**: GitHub preview branches on ODIN server
- **Symptoms**: PromotedCategoriesQuery and SliderItemsQuery return `[]` on first load
- **Pattern**: Works perfectly after reload
- **Other queries**: Work fine on first load
- **No errors**: Status 200, structurally valid GraphQL responses

### What We've Ruled Out
- ❌ **Cache logic issues**: Thoroughly analyzed, cache miss detection is correct
- ❌ **Cache key collisions**: Query names prevent collisions between different queries
- ❌ **Redis caching problems**: Cache miss logs are accurate

### What We're Investigating
- 🔍 **Domain-specific data dependencies**: Both queries need specific DB records
- 🔍 **Data seeding issues**: Preview branches might lack required data
- 🔍 **Query timing**: Race conditions in service initialization
- 🔍 **Filtering problems**: Date/visibility filters might be too restrictive

### Key Technical Insights
1. **Cache miss indicates PHP backend called**: Issue is in PHP logic, not Redis
2. **Domain-specific queries**: Both failing queries have special requirements
3. **INNER JOIN dependencies**: PromotedCategoriesQuery needs TopCategory records
4. **Date/visibility filters**: SliderItemsQuery has complex filtering logic

## 📝 Session Templates

### Starting New Session Template
```
# Continuing GraphQL Investigation

## Current Status Check
- [ ] Read session-investigation-findings.md for latest status
- [ ] Review root-cause-debugging-plan.md for current iteration
- [ ] Check what work is pending from previous session
- [ ] Understand current hypothesis and evidence

## My Session Plan
1. [Fill in your plan based on current status]
2. [Add specific tasks for this session]
3. [Note any new approaches or ideas]

## Expected Outcomes
- [What you expect to accomplish]
- [What decisions you'll need to make]
- [What the next agent should focus on]
```

### Ending Session Template
```
# Session Summary

## Work Completed
- [ ] [List major accomplishments]
- [ ] [Files modified or created]
- [ ] [Tests or validations performed]

## Key Findings
- [New evidence discovered]
- [Hypotheses confirmed or disproven]
- [Unexpected discoveries]

## Current Status
- [Where investigation stands now]
- [What's working/not working]
- [Confidence level in current approach]

## Next Steps for Future Agent
1. [Immediate next actions]
2. [Medium-term investigation plan]
3. [Decisions that need to be made]

## Files to Update
- [ ] session-investigation-findings.md (add findings)
- [ ] root-cause-debugging-plan.md (update iteration status)
- [ ] session-continuity-framework.md (add session log)
```

## 🚀 Quick Reference: Essential Commands

### Local Testing
```bash
# User visits local website to trigger GraphQL queries
# Then check logs
docker compose logs php-fpm | grep -E "(PromotedCategories|SliderItems)"
```

### ODIN Server Analysis
```bash
# tmux automation pattern
marker="TMUX_MARKER_$(date +%s%N)"
tmux send-keys -t %14 "echo '=== $marker START ==='" Enter
sleep 0.5
tmux send-keys -t %14 "cd ~/actions-runner/_work/shopsys/shopsys/jm-after-build-bug-fix-ssp-3495" Enter
sleep 2
tmux send-keys -t %14 "echo '=== $marker END ==='" Enter
sleep 0.5
tmux capture-pane -t %14 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

## 🎯 Success Criteria

### Investigation Complete When:
- ✅ Root cause definitively identified
- ✅ Fix implemented and tested
- ✅ Issue no longer reproduces
- ✅ Understanding documented for future reference

### Investigation Failed If:
- ❌ Can't reproduce the issue consistently
- ❌ Logs provide no useful information
- ❌ Issue disappears (heisenbug)
- ❌ Multiple iterations without progress

---

**Framework Version**: 1.0
**Last Updated**: 2025-07-18
**Next Review**: After each major iteration